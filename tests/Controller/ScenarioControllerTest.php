<?php

namespace App\Tests\Controller;

use App\Entity\Scenario;
use App\Repository\ScenarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ScenarioControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Scenario> $scenarioRepository */
    private EntityRepository $scenarioRepository;
    private string $path = '/scenario/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->scenarioRepository = $this->manager->getRepository(Scenario::class);

        foreach ($this->scenarioRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Scenario index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'scenario[description]' => 'Testing',
            'scenario[animation]' => 'Testing',
            'scenario[emotion]' => 'Testing',
        ]);

        self::assertResponseRedirects('/scenario');

        self::assertSame(1, $this->scenarioRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Scenario();
        $fixture->setDescription('My Title');
        $fixture->setAnimation('My Title');
        $fixture->setEmotion('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Scenario');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Scenario();
        $fixture->setDescription('Value');
        $fixture->setAnimation('Value');
        $fixture->setEmotion('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'scenario[description]' => 'Something New',
            'scenario[animation]' => 'Something New',
            'scenario[emotion]' => 'Something New',
        ]);

        self::assertResponseRedirects('/scenario');

        $fixture = $this->scenarioRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getAnimation());
        self::assertSame('Something New', $fixture[0]->getEmotion());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Scenario();
        $fixture->setDescription('Value');
        $fixture->setAnimation('Value');
        $fixture->setEmotion('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/scenario');
        self::assertSame(0, $this->scenarioRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
