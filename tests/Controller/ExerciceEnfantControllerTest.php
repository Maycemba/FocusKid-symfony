<?php

namespace App\Tests\Controller;

use App\Entity\ExerciceEnfant;
use App\Repository\ExerciceEnfantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExerciceEnfantControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<ExerciceEnfant> $exerciceEnfantRepository */
    private EntityRepository $exerciceEnfantRepository;
    private string $path = '/exercice/enfant/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->exerciceEnfantRepository = $this->manager->getRepository(ExerciceEnfant::class);

        foreach ($this->exerciceEnfantRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ExerciceEnfant index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'exercice_enfant[exercice_id]' => 'Testing',
            'exercice_enfant[enfant_id]' => 'Testing',
            'exercice_enfant[date_attribution]' => 'Testing',
        ]);

        self::assertResponseRedirects('/exercice/enfant');

        self::assertSame(1, $this->exerciceEnfantRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new ExerciceEnfant();
        $fixture->setExerciceId('My Title');
        $fixture->setEnfantId('My Title');
        $fixture->setDateAttribution('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ExerciceEnfant');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new ExerciceEnfant();
        $fixture->setExerciceId('Value');
        $fixture->setEnfantId('Value');
        $fixture->setDateAttribution('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'exercice_enfant[exercice_id]' => 'Something New',
            'exercice_enfant[enfant_id]' => 'Something New',
            'exercice_enfant[date_attribution]' => 'Something New',
        ]);

        self::assertResponseRedirects('/exercice/enfant');

        $fixture = $this->exerciceEnfantRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getExerciceId());
        self::assertSame('Something New', $fixture[0]->getEnfantId());
        self::assertSame('Something New', $fixture[0]->getDateAttribution());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new ExerciceEnfant();
        $fixture->setExerciceId('Value');
        $fixture->setEnfantId('Value');
        $fixture->setDateAttribution('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/exercice/enfant');
        self::assertSame(0, $this->exerciceEnfantRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
