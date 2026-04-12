<?php

namespace App\Tests\Controller;

use App\Entity\CarnetEducatif;
use App\Repository\CarnetEducatifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CarnetEducatifControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<CarnetEducatif> $carnetEducatifRepository */
    private EntityRepository $carnetEducatifRepository;
    private string $path = '/carnet/educatif/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->carnetEducatifRepository = $this->manager->getRepository(CarnetEducatif::class);

        foreach ($this->carnetEducatifRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('CarnetEducatif index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'carnet_educatif[date_etude]' => 'Testing',
            'carnet_educatif[heure_debut]' => 'Testing',
            'carnet_educatif[heure_fin]' => 'Testing',
            'carnet_educatif[duree_totale]' => 'Testing',
            'carnet_educatif[lieu]' => 'Testing',
            'carnet_educatif[matiere]' => 'Testing',
            'carnet_educatif[type_activite]' => 'Testing',
            'carnet_educatif[niveau_difficulte]' => 'Testing',
            'carnet_educatif[niveau_concentration]' => 'Testing',
            'carnet_educatif[niveau_agitation]' => 'Testing',
            'carnet_educatif[nombre_interruptions]' => 'Testing',
            'carnet_educatif[temps_avant_perte_concentration]' => 'Testing',
            'carnet_educatif[travaille_seul]' => 'Testing',
            'carnet_educatif[demande_aide]' => 'Testing',
            'carnet_educatif[niveau_autonomie]' => 'Testing',
            'carnet_educatif[travail_termine]' => 'Testing',
            'carnet_educatif[difficultes]' => 'Testing',
            'carnet_educatif[points_positifs]' => 'Testing',
            'carnet_educatif[created_at]' => 'Testing',
            'carnet_educatif[utilisateur]' => 'Testing',
        ]);

        self::assertResponseRedirects('/carnet/educatif');

        self::assertSame(1, $this->carnetEducatifRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new CarnetEducatif();
        $fixture->setDateEtude('My Title');
        $fixture->setHeureDebut('My Title');
        $fixture->setHeureFin('My Title');
        $fixture->setDureeTotale('My Title');
        $fixture->setLieu('My Title');
        $fixture->setMatiere('My Title');
        $fixture->setTypeActivite('My Title');
        $fixture->setNiveauDifficulte('My Title');
        $fixture->setNiveauConcentration('My Title');
        $fixture->setNiveauAgitation('My Title');
        $fixture->setNombreInterruptions('My Title');
        $fixture->setTempsAvantPerteConcentration('My Title');
        $fixture->setTravailleSeul('My Title');
        $fixture->setDemandeAide('My Title');
        $fixture->setNiveauAutonomie('My Title');
        $fixture->setTravailTermine('My Title');
        $fixture->setDifficultes('My Title');
        $fixture->setPointsPositifs('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setUtilisateur('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('CarnetEducatif');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new CarnetEducatif();
        $fixture->setDateEtude('Value');
        $fixture->setHeureDebut('Value');
        $fixture->setHeureFin('Value');
        $fixture->setDureeTotale('Value');
        $fixture->setLieu('Value');
        $fixture->setMatiere('Value');
        $fixture->setTypeActivite('Value');
        $fixture->setNiveauDifficulte('Value');
        $fixture->setNiveauConcentration('Value');
        $fixture->setNiveauAgitation('Value');
        $fixture->setNombreInterruptions('Value');
        $fixture->setTempsAvantPerteConcentration('Value');
        $fixture->setTravailleSeul('Value');
        $fixture->setDemandeAide('Value');
        $fixture->setNiveauAutonomie('Value');
        $fixture->setTravailTermine('Value');
        $fixture->setDifficultes('Value');
        $fixture->setPointsPositifs('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setUtilisateur('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'carnet_educatif[date_etude]' => 'Something New',
            'carnet_educatif[heure_debut]' => 'Something New',
            'carnet_educatif[heure_fin]' => 'Something New',
            'carnet_educatif[duree_totale]' => 'Something New',
            'carnet_educatif[lieu]' => 'Something New',
            'carnet_educatif[matiere]' => 'Something New',
            'carnet_educatif[type_activite]' => 'Something New',
            'carnet_educatif[niveau_difficulte]' => 'Something New',
            'carnet_educatif[niveau_concentration]' => 'Something New',
            'carnet_educatif[niveau_agitation]' => 'Something New',
            'carnet_educatif[nombre_interruptions]' => 'Something New',
            'carnet_educatif[temps_avant_perte_concentration]' => 'Something New',
            'carnet_educatif[travaille_seul]' => 'Something New',
            'carnet_educatif[demande_aide]' => 'Something New',
            'carnet_educatif[niveau_autonomie]' => 'Something New',
            'carnet_educatif[travail_termine]' => 'Something New',
            'carnet_educatif[difficultes]' => 'Something New',
            'carnet_educatif[points_positifs]' => 'Something New',
            'carnet_educatif[created_at]' => 'Something New',
            'carnet_educatif[utilisateur]' => 'Something New',
        ]);

        self::assertResponseRedirects('/carnet/educatif');

        $fixture = $this->carnetEducatifRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDateEtude());
        self::assertSame('Something New', $fixture[0]->getHeureDebut());
        self::assertSame('Something New', $fixture[0]->getHeureFin());
        self::assertSame('Something New', $fixture[0]->getDureeTotale());
        self::assertSame('Something New', $fixture[0]->getLieu());
        self::assertSame('Something New', $fixture[0]->getMatiere());
        self::assertSame('Something New', $fixture[0]->getTypeActivite());
        self::assertSame('Something New', $fixture[0]->getNiveauDifficulte());
        self::assertSame('Something New', $fixture[0]->getNiveauConcentration());
        self::assertSame('Something New', $fixture[0]->getNiveauAgitation());
        self::assertSame('Something New', $fixture[0]->getNombreInterruptions());
        self::assertSame('Something New', $fixture[0]->getTempsAvantPerteConcentration());
        self::assertSame('Something New', $fixture[0]->getTravailleSeul());
        self::assertSame('Something New', $fixture[0]->getDemandeAide());
        self::assertSame('Something New', $fixture[0]->getNiveauAutonomie());
        self::assertSame('Something New', $fixture[0]->getTravailTermine());
        self::assertSame('Something New', $fixture[0]->getDifficultes());
        self::assertSame('Something New', $fixture[0]->getPointsPositifs());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new CarnetEducatif();
        $fixture->setDateEtude('Value');
        $fixture->setHeureDebut('Value');
        $fixture->setHeureFin('Value');
        $fixture->setDureeTotale('Value');
        $fixture->setLieu('Value');
        $fixture->setMatiere('Value');
        $fixture->setTypeActivite('Value');
        $fixture->setNiveauDifficulte('Value');
        $fixture->setNiveauConcentration('Value');
        $fixture->setNiveauAgitation('Value');
        $fixture->setNombreInterruptions('Value');
        $fixture->setTempsAvantPerteConcentration('Value');
        $fixture->setTravailleSeul('Value');
        $fixture->setDemandeAide('Value');
        $fixture->setNiveauAutonomie('Value');
        $fixture->setTravailTermine('Value');
        $fixture->setDifficultes('Value');
        $fixture->setPointsPositifs('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setUtilisateur('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/carnet/educatif');
        self::assertSame(0, $this->carnetEducatifRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
