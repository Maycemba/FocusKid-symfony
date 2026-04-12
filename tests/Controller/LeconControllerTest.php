<?php

namespace App\Tests\Controller;

use App\Entity\Lecon;
use App\Repository\LeconRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LeconControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Lecon> $leconRepository */
    private EntityRepository $leconRepository;
    private string $path = '/lecon/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->leconRepository = $this->manager->getRepository(Lecon::class);

        foreach ($this->leconRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Lecon index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'lecon[titre_lecon]' => 'Testing',
            'lecon[contenu]' => 'Testing',
            'lecon[cour]' => 'Testing',
        ]);

        self::assertResponseRedirects('/lecon');

        self::assertSame(1, $this->leconRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Lecon();
        $fixture->setTitreLecon('My Title');
        $fixture->setContenu('My Title');
        $fixture->setCour('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Lecon');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Lecon();
        $fixture->setTitreLecon('Value');
        $fixture->setContenu('Value');
        $fixture->setCour('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'lecon[titre_lecon]' => 'Something New',
            'lecon[contenu]' => 'Something New',
            'lecon[cour]' => 'Something New',
        ]);

        self::assertResponseRedirects('/lecon');

        $fixture = $this->leconRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitreLecon());
        self::assertSame('Something New', $fixture[0]->getContenu());
        self::assertSame('Something New', $fixture[0]->getCour());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Lecon();
        $fixture->setTitreLecon('Value');
        $fixture->setContenu('Value');
        $fixture->setCour('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/lecon');
        self::assertSame(0, $this->leconRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
