<?php

namespace App\Tests\Controller;

use App\Entity\Cour;
use App\Repository\CourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CourControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Cour> $courRepository */
    private EntityRepository $courRepository;
    private string $path = '/cour/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->courRepository = $this->manager->getRepository(Cour::class);

        foreach ($this->courRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Cour index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'cour[titre]' => 'Testing',
            'cour[niveau]' => 'Testing',
            'cour[description]' => 'Testing',
            'cour[formateur]' => 'Testing',
            'cour[statut]' => 'Testing',
        ]);

        self::assertResponseRedirects('/cour');

        self::assertSame(1, $this->courRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Cour();
        $fixture->setTitre('My Title');
        $fixture->setNiveau('My Title');
        $fixture->setDescription('My Title');
        $fixture->setFormateur('My Title');
        $fixture->setStatut('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Cour');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Cour();
        $fixture->setTitre('Value');
        $fixture->setNiveau('Value');
        $fixture->setDescription('Value');
        $fixture->setFormateur('Value');
        $fixture->setStatut('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'cour[titre]' => 'Something New',
            'cour[niveau]' => 'Something New',
            'cour[description]' => 'Something New',
            'cour[formateur]' => 'Something New',
            'cour[statut]' => 'Something New',
        ]);

        self::assertResponseRedirects('/cour');

        $fixture = $this->courRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitre());
        self::assertSame('Something New', $fixture[0]->getNiveau());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getFormateur());
        self::assertSame('Something New', $fixture[0]->getStatut());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Cour();
        $fixture->setTitre('Value');
        $fixture->setNiveau('Value');
        $fixture->setDescription('Value');
        $fixture->setFormateur('Value');
        $fixture->setStatut('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/cour');
        self::assertSame(0, $this->courRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
