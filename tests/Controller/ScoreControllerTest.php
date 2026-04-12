<?php

namespace App\Tests\Controller;

use App\Entity\Score;
use App\Repository\ScoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ScoreControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Score> $scoreRepository */
    private EntityRepository $scoreRepository;
    private string $path = '/score/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->scoreRepository = $this->manager->getRepository(Score::class);

        foreach ($this->scoreRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Score index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'score[points]' => 'Testing',
            'score[date_partie]' => 'Testing',
            'score[utilisateur]' => 'Testing',
            'score[jeu]' => 'Testing',
        ]);

        self::assertResponseRedirects('/score');

        self::assertSame(1, $this->scoreRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Score();
        $fixture->setPoints('My Title');
        $fixture->setDatePartie('My Title');
        $fixture->setUtilisateur('My Title');
        $fixture->setJeu('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Score');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Score();
        $fixture->setPoints('Value');
        $fixture->setDatePartie('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setJeu('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'score[points]' => 'Something New',
            'score[date_partie]' => 'Something New',
            'score[utilisateur]' => 'Something New',
            'score[jeu]' => 'Something New',
        ]);

        self::assertResponseRedirects('/score');

        $fixture = $this->scoreRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getPoints());
        self::assertSame('Something New', $fixture[0]->getDatePartie());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());
        self::assertSame('Something New', $fixture[0]->getJeu());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Score();
        $fixture->setPoints('Value');
        $fixture->setDatePartie('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setJeu('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/score');
        self::assertSame(0, $this->scoreRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
