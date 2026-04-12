<?php

namespace App\Tests\Controller;

use App\Entity\ReponseUserQuiz;
use App\Repository\ReponseUserQuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ReponseUserQuizControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<ReponseUserQuiz> $reponseUserQuizRepository */
    private EntityRepository $reponseUserQuizRepository;
    private string $path = '/reponse/user/quiz/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->reponseUserQuizRepository = $this->manager->getRepository(ReponseUserQuiz::class);

        foreach ($this->reponseUserQuizRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ReponseUserQuiz index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'reponse_user_quiz[est_correcte]' => 'Testing',
            'reponse_user_quiz[temps_reponse]' => 'Testing',
            'reponse_user_quiz[date_reponse]' => 'Testing',
            'reponse_user_quiz[utilisateur]' => 'Testing',
            'reponse_user_quiz[quiz]' => 'Testing',
            'reponse_user_quiz[questionQuiz]' => 'Testing',
            'reponse_user_quiz[correctionQuiz]' => 'Testing',
        ]);

        self::assertResponseRedirects('/reponse/user/quiz');

        self::assertSame(1, $this->reponseUserQuizRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new ReponseUserQuiz();
        $fixture->setEstCorrecte('My Title');
        $fixture->setTempsReponse('My Title');
        $fixture->setDateReponse('My Title');
        $fixture->setUtilisateur('My Title');
        $fixture->setQuiz('My Title');
        $fixture->setQuestionQuiz('My Title');
        $fixture->setCorrectionQuiz('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ReponseUserQuiz');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new ReponseUserQuiz();
        $fixture->setEstCorrecte('Value');
        $fixture->setTempsReponse('Value');
        $fixture->setDateReponse('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setQuiz('Value');
        $fixture->setQuestionQuiz('Value');
        $fixture->setCorrectionQuiz('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'reponse_user_quiz[est_correcte]' => 'Something New',
            'reponse_user_quiz[temps_reponse]' => 'Something New',
            'reponse_user_quiz[date_reponse]' => 'Something New',
            'reponse_user_quiz[utilisateur]' => 'Something New',
            'reponse_user_quiz[quiz]' => 'Something New',
            'reponse_user_quiz[questionQuiz]' => 'Something New',
            'reponse_user_quiz[correctionQuiz]' => 'Something New',
        ]);

        self::assertResponseRedirects('/reponse/user/quiz');

        $fixture = $this->reponseUserQuizRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getEstCorrecte());
        self::assertSame('Something New', $fixture[0]->getTempsReponse());
        self::assertSame('Something New', $fixture[0]->getDateReponse());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());
        self::assertSame('Something New', $fixture[0]->getQuiz());
        self::assertSame('Something New', $fixture[0]->getQuestionQuiz());
        self::assertSame('Something New', $fixture[0]->getCorrectionQuiz());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new ReponseUserQuiz();
        $fixture->setEstCorrecte('Value');
        $fixture->setTempsReponse('Value');
        $fixture->setDateReponse('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setQuiz('Value');
        $fixture->setQuestionQuiz('Value');
        $fixture->setCorrectionQuiz('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/reponse/user/quiz');
        self::assertSame(0, $this->reponseUserQuizRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
