<?php

namespace App\Tests\Controller;

use App\Entity\Quiz;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class QuizControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Quiz> $quizRepository */
    private EntityRepository $quizRepository;
    private string $path = '/quiz/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->quizRepository = $this->manager->getRepository(Quiz::class);

        foreach ($this->quizRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Quiz index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'quiz[titre]' => 'Testing',
            'quiz[description]' => 'Testing',
            'quiz[difficulte]' => 'Testing',
            'quiz[points_total]' => 'Testing',
            'quiz[duree_totale]' => 'Testing',
            'quiz[est_actif]' => 'Testing',
            'quiz[cour]' => 'Testing',
        ]);

        self::assertResponseRedirects('/quiz');

        self::assertSame(1, $this->quizRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Quiz();
        $fixture->setTitre('My Title');
        $fixture->setDescription('My Title');
        $fixture->setDifficulte('My Title');
        $fixture->setPointsTotal('My Title');
        $fixture->setDureeTotale('My Title');
        $fixture->setEstActif('My Title');
        $fixture->setCour('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Quiz');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Quiz();
        $fixture->setTitre('Value');
        $fixture->setDescription('Value');
        $fixture->setDifficulte('Value');
        $fixture->setPointsTotal('Value');
        $fixture->setDureeTotale('Value');
        $fixture->setEstActif('Value');
        $fixture->setCour('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'quiz[titre]' => 'Something New',
            'quiz[description]' => 'Something New',
            'quiz[difficulte]' => 'Something New',
            'quiz[points_total]' => 'Something New',
            'quiz[duree_totale]' => 'Something New',
            'quiz[est_actif]' => 'Something New',
            'quiz[cour]' => 'Something New',
        ]);

        self::assertResponseRedirects('/quiz');

        $fixture = $this->quizRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitre());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getDifficulte());
        self::assertSame('Something New', $fixture[0]->getPointsTotal());
        self::assertSame('Something New', $fixture[0]->getDureeTotale());
        self::assertSame('Something New', $fixture[0]->getEstActif());
        self::assertSame('Something New', $fixture[0]->getCour());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Quiz();
        $fixture->setTitre('Value');
        $fixture->setDescription('Value');
        $fixture->setDifficulte('Value');
        $fixture->setPointsTotal('Value');
        $fixture->setDureeTotale('Value');
        $fixture->setEstActif('Value');
        $fixture->setCour('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/quiz');
        self::assertSame(0, $this->quizRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
