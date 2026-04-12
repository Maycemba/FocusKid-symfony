<?php

namespace App\Tests\Controller;

use App\Entity\CorrectionQuiz;
use App\Repository\CorrectionQuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CorrectionQuizControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<CorrectionQuiz> $correctionQuizRepository */
    private EntityRepository $correctionQuizRepository;
    private string $path = '/correction/quiz/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->correctionQuizRepository = $this->manager->getRepository(CorrectionQuiz::class);

        foreach ($this->correctionQuizRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('CorrectionQuiz index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'correction_quiz[contenu]' => 'Testing',
            'correction_quiz[est_correcte]' => 'Testing',
            'correction_quiz[questionQuiz]' => 'Testing',
        ]);

        self::assertResponseRedirects('/correction/quiz');

        self::assertSame(1, $this->correctionQuizRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new CorrectionQuiz();
        $fixture->setContenu('My Title');
        $fixture->setEstCorrecte('My Title');
        $fixture->setQuestionQuiz('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('CorrectionQuiz');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new CorrectionQuiz();
        $fixture->setContenu('Value');
        $fixture->setEstCorrecte('Value');
        $fixture->setQuestionQuiz('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'correction_quiz[contenu]' => 'Something New',
            'correction_quiz[est_correcte]' => 'Something New',
            'correction_quiz[questionQuiz]' => 'Something New',
        ]);

        self::assertResponseRedirects('/correction/quiz');

        $fixture = $this->correctionQuizRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getContenu());
        self::assertSame('Something New', $fixture[0]->getEstCorrecte());
        self::assertSame('Something New', $fixture[0]->getQuestionQuiz());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new CorrectionQuiz();
        $fixture->setContenu('Value');
        $fixture->setEstCorrecte('Value');
        $fixture->setQuestionQuiz('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/correction/quiz');
        self::assertSame(0, $this->correctionQuizRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
