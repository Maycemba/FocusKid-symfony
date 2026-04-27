<?php

namespace App\Controller;

use App\Entity\Cour;
use App\Entity\Lecon;
use App\Form\LeconType;
use App\Repository\CourRepository;
use App\Repository\LeconRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/lecon')]
final class LeconController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $groqApiKey
    ) {}

    #[Route(name: 'app_lecon_index', methods: ['GET'])]
    public function index(LeconRepository $leconRepository): Response
    {
        return $this->render('lecon/index.html.twig', [
            'lecons' => $leconRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_lecon_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        CourRepository $courRepository,
        MailerInterface $mailer
    ): Response {
        $lecon   = new Lecon();
        $cour    = null;
        $idCours = $request->query->getInt('id_cours');

        if ($idCours > 0) {
            $cour = $courRepository->find($idCours);
            if ($cour instanceof Cour) {
                $lecon->setCour($cour);
            }
        }

        $form = $this->createForm(LeconType::class, $lecon, [
            'cour_locked' => $cour instanceof Cour,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lecon);
            $entityManager->flush();

            $rawEmails = $request->request->get('email_parent', '');
            $emails    = $this->parseEmails($rawEmails);

            file_put_contents(
                __DIR__ . '/../../mail_debug.log',
                date('Y-m-d H:i:s') . " | raw: $rawEmails | parsed: " . implode(',', $emails) . "\n",
                FILE_APPEND
            );

            if (!empty($emails)) {
                $iaData   = $this->generateLeconSummary($lecon);
                $leconUrl = $this->generateUrl(
                    'app_lecon_show',
                    ['id_lecon' => $lecon->getIdLecon()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $sent   = 0;
                $errors = [];

                foreach ($emails as $emailParent) {
                    try {
                        $this->sendParentEmail(
                            $mailer,
                            $emailParent,
                            $lecon,
                            $iaData,
                            $leconUrl
                        );
                        $sent++;
                    } catch (\Throwable $e) {
                        $errors[] = $emailParent . ' : ' . $e->getMessage();

                        file_put_contents(
                            'mail_error.log',
                            date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . "\n",
                            FILE_APPEND
                        );
                    }
                }

                if ($sent > 0) {
                    $this->addFlash('success', "Email envoye a $sent parent(s) avec succes !");
                }

                foreach ($errors as $err) {
                    $this->addFlash('warning', "Echec d'envoi pour : $err");
                }
            }

            return $this->redirectToRoute('app_lecon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lecon/new.html.twig', [
            'lecon' => $lecon,
            'form'  => $form,
            'cour'  => $cour,
        ]);
    }

    private function parseEmails(string $raw): array
    {
        return array_values(array_filter(
            array_map('trim', explode(',', $raw)),
            fn(string $e) => filter_var($e, FILTER_VALIDATE_EMAIL) !== false
        ));
    }

    private function generateLeconSummary(Lecon $lecon): array
    {
        $titre   = $lecon->getTitreLecon() ?? '';
        $contenu = $lecon->getContenu() ?? '';
        $niveau  = $lecon->getCour()?->getNiveau() ?? '';

        $prompt = <<<PROMPT
Tu es un assistant pedagogique. Genere UNIQUEMENT un objet JSON valide (sans texte autour, sans backticks) :
{
  "resume": "resume simple en 1 phrase pour un parent",
  "objectif": "objectif pedagogique en 1 phrase",
  "duree": 15
}
Titre : $titre
Niveau : $niveau
Contenu : $contenu
PROMPT;

        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'llama-3.3-70b-versatile',
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.3,
                    'max_tokens'  => 200,
                ],
            ]);

            $result = $response->toArray();
            $text   = $result['choices'][0]['message']['content'] ?? '{}';
            $text   = preg_replace('/```json|```/', '', $text);
            $data   = json_decode(trim($text), true);

            return [
                'resume'   => $data['resume'] ?? 'Votre enfant va apprendre ' . $titre,
                'objectif' => $data['objectif'] ?? 'Comprendre les bases de ' . $titre,
                'duree'    => $data['duree'] ?? 15,
            ];
        } catch (\Throwable) {
            return [
                'resume'   => 'Votre enfant va apprendre : ' . $titre,
                'objectif' => 'Comprendre les bases de ' . $titre,
                'duree'    => 15,
            ];
        }
    }

    private function sendParentEmail(
        MailerInterface $mailer,
        string $emailParent,
        Lecon $lecon,
        array $iaData,
        string $leconUrl
    ): void {
        $titre  = $lecon->getTitreLecon() ?? 'une nouvelle lecon';
        $niveau = $lecon->getCour()?->getNiveau() ?? '';
        $cours  = $lecon->getCour()?->getTitre() ?? '';
        $duree  = $iaData['duree'];

        $coursInfo  = $cours !== '' ? " dans le cours <strong>{$cours}</strong>" : '';
        $niveauInfo = $niveau !== '' ? " (Niveau : <strong>{$niveau}</strong>)" : '';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Nouveau cours publie - FocusKids</title>
</head>
<body style="margin:0;padding:0;background:#f0f4ff;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4ff;padding:40px 0;">
  <tr>
    <td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.1);">
        <tr>
          <td style="background:linear-gradient(135deg,#667eea,#764ba2);padding:36px 40px;text-align:center;">
            <div style="font-size:44px;margin-bottom:8px;">🎓</div>
            <h1 style="color:#fff;margin:0;font-size:26px;font-weight:700;">FocusKids</h1>
            <p style="color:rgba(255,255,255,.8);margin:6px 0 0;font-size:14px;">Plateforme d'apprentissage intelligente</p>
          </td>
        </tr>

        <tr>
          <td style="padding:36px 40px 20px;">
            <h2 style="color:#2d2d2d;margin:0 0 12px;font-size:20px;">Nouvelle lecon publiee</h2>
            <p style="color:#555;font-size:15px;line-height:1.7;margin:0;">
              Bonjour,<br><br>
              Une nouvelle lecon a ete publiee pour votre enfant :
              <strong style="color:#667eea;">{$titre}</strong>{$coursInfo}{$niveauInfo}.
            </p>
          </td>
        </tr>

        <tr>
          <td style="padding:10px 40px 28px;">
            <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td width="31%" style="background:#f0f4ff;border-radius:14px;padding:18px;text-align:center;vertical-align:top;">
                  <div style="font-size:30px;">🧠</div>
                  <div style="font-size:10px;color:#999;font-weight:700;text-transform:uppercase;margin:8px 0 5px;letter-spacing:.5px;">Resume</div>
                  <div style="font-size:13px;color:#444;line-height:1.5;">{$iaData['resume']}</div>
                </td>
                <td width="3%"></td>
                <td width="31%" style="background:#fff4e6;border-radius:14px;padding:18px;text-align:center;vertical-align:top;">
                  <div style="font-size:30px;">🎯</div>
                  <div style="font-size:10px;color:#999;font-weight:700;text-transform:uppercase;margin:8px 0 5px;letter-spacing:.5px;">Objectif</div>
                  <div style="font-size:13px;color:#444;line-height:1.5;">{$iaData['objectif']}</div>
                </td>
                <td width="3%"></td>
                <td width="31%" style="background:#e8f8f0;border-radius:14px;padding:18px;text-align:center;vertical-align:top;">
                  <div style="font-size:30px;">⏱️</div>
                  <div style="font-size:10px;color:#999;font-weight:700;text-transform:uppercase;margin:8px 0 5px;letter-spacing:.5px;">Duree estimee</div>
                  <div style="font-size:28px;font-weight:800;color:#38a169;">{$duree} min</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <tr>
          <td style="padding:0 40px 30px;">
            <p style="margin:0;color:#666;font-size:14px;line-height:1.7;text-align:center;">
              Ceci est un email d'information pour vous avertir qu'un nouveau contenu est disponible sur FocusKids.
            </p>
          </td>
        </tr>

        <tr>
          <td style="background:#f8f9fa;padding:18px 40px;text-align:center;border-top:1px solid #eee;">
            <p style="margin:0;font-size:12px;color:#aaa;">FocusKids - Plateforme educative intelligente</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;

        $email = (new Email())
            ->from(new Address($_ENV['MAILER_FROM'] ?? 'focuskids@example.com', 'FocusKids'))
            ->to($emailParent)
            ->subject("Nouvelle lecon publiee : {$titre}")
            ->html($html);

        $mailer->send($email);
    }

    #[Route('/{id_lecon}', name: 'app_lecon_show', methods: ['GET'])]
    public function show(Lecon $lecon): Response
    {
        return $this->render('lecon/show.html.twig', [
            'lecon' => $lecon,
        ]);
    }

    #[Route('/{id_lecon}/edit', name: 'app_lecon_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lecon $lecon, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LeconType::class, $lecon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_lecon_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lecon/edit.html.twig', [
            'lecon' => $lecon,
            'form'  => $form,
        ]);
    }

    #[Route('/{id_lecon}', name: 'app_lecon_delete', methods: ['POST'])]
    public function delete(Request $request, Lecon $lecon, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $lecon->getId_lecon(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($lecon);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lecon_index', [], Response::HTTP_SEE_OTHER);
    }
}