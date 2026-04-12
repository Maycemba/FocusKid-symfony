<?php

namespace App\Tests\Controller;

use App\Entity\HumeurJournaliere;
use App\Repository\HumeurJournaliereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HumeurJournaliereControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<HumeurJournaliere> $humeurJournaliereRepository */
    private EntityRepository $humeurJournaliereRepository;
    private string $path = '/humeur/journaliere/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->humeurJournaliereRepository = $this->manager->getRepository(HumeurJournaliere::class);

        foreach ($this->humeurJournaliereRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('HumeurJournaliere index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'humeur_journaliere[dateHeure]' => 'Testing',
            'humeur_journaliere[emotion]' => 'Testing',
        ]);

        self::assertResponseRedirects('/humeur/journaliere');

        self::assertSame(1, $this->humeurJournaliereRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new HumeurJournaliere();
        $fixture->setDateHeure('My Title');
        $fixture->setEmotion('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('HumeurJournaliere');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new HumeurJournaliere();
        $fixture->setDateHeure('Value');
        $fixture->setEmotion('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'humeur_journaliere[dateHeure]' => 'Something New',
            'humeur_journaliere[emotion]' => 'Something New',
        ]);

        self::assertResponseRedirects('/humeur/journaliere');

        $fixture = $this->humeurJournaliereRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDateHeure());
        self::assertSame('Something New', $fixture[0]->getEmotion());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new HumeurJournaliere();
        $fixture->setDateHeure('Value');
        $fixture->setEmotion('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/humeur/journaliere');
        self::assertSame(0, $this->humeurJournaliereRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
