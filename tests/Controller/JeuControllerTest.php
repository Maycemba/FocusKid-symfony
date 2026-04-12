<?php

namespace App\Tests\Controller;

use App\Entity\Jeu;
use App\Repository\JeuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class JeuControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Jeu> $jeuRepository */
    private EntityRepository $jeuRepository;
    private string $path = '/jeu/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->jeuRepository = $this->manager->getRepository(Jeu::class);

        foreach ($this->jeuRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Jeu index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'jeu[titre]' => 'Testing',
            'jeu[type]' => 'Testing',
            'jeu[niveau]' => 'Testing',
        ]);

        self::assertResponseRedirects('/jeu');

        self::assertSame(1, $this->jeuRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Jeu();
        $fixture->setTitre('My Title');
        $fixture->setType('My Title');
        $fixture->setNiveau('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Jeu');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Jeu();
        $fixture->setTitre('Value');
        $fixture->setType('Value');
        $fixture->setNiveau('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'jeu[titre]' => 'Something New',
            'jeu[type]' => 'Something New',
            'jeu[niveau]' => 'Something New',
        ]);

        self::assertResponseRedirects('/jeu');

        $fixture = $this->jeuRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitre());
        self::assertSame('Something New', $fixture[0]->getType());
        self::assertSame('Something New', $fixture[0]->getNiveau());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Jeu();
        $fixture->setTitre('Value');
        $fixture->setType('Value');
        $fixture->setNiveau('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/jeu');
        self::assertSame(0, $this->jeuRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
