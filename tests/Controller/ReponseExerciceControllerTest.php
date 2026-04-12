<?php

namespace App\Tests\Controller;

use App\Entity\ReponseExercice;
use App\Repository\ReponseExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ReponseExerciceControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<ReponseExercice> $reponseExerciceRepository */
    private EntityRepository $reponseExerciceRepository;
    private string $path = '/reponse/exercice/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->reponseExerciceRepository = $this->manager->getRepository(ReponseExercice::class);

        foreach ($this->reponseExerciceRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ReponseExercice index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'reponse_exercice[exercice_id]' => 'Testing',
            'reponse_exercice[enfant_id]' => 'Testing',
            'reponse_exercice[score]' => 'Testing',
            'reponse_exercice[temps_passe]' => 'Testing',
            'reponse_exercice[reponses]' => 'Testing',
            'reponse_exercice[reussite]' => 'Testing',
            'reponse_exercice[date_passage]' => 'Testing',
        ]);

        self::assertResponseRedirects('/reponse/exercice');

        self::assertSame(1, $this->reponseExerciceRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new ReponseExercice();
        $fixture->setExerciceId('My Title');
        $fixture->setEnfantId('My Title');
        $fixture->setScore('My Title');
        $fixture->setTempsPasse('My Title');
        $fixture->setReponses('My Title');
        $fixture->setReussite('My Title');
        $fixture->setDatePassage('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ReponseExercice');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new ReponseExercice();
        $fixture->setExerciceId('Value');
        $fixture->setEnfantId('Value');
        $fixture->setScore('Value');
        $fixture->setTempsPasse('Value');
        $fixture->setReponses('Value');
        $fixture->setReussite('Value');
        $fixture->setDatePassage('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'reponse_exercice[exercice_id]' => 'Something New',
            'reponse_exercice[enfant_id]' => 'Something New',
            'reponse_exercice[score]' => 'Something New',
            'reponse_exercice[temps_passe]' => 'Something New',
            'reponse_exercice[reponses]' => 'Something New',
            'reponse_exercice[reussite]' => 'Something New',
            'reponse_exercice[date_passage]' => 'Something New',
        ]);

        self::assertResponseRedirects('/reponse/exercice');

        $fixture = $this->reponseExerciceRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getExerciceId());
        self::assertSame('Something New', $fixture[0]->getEnfantId());
        self::assertSame('Something New', $fixture[0]->getScore());
        self::assertSame('Something New', $fixture[0]->getTempsPasse());
        self::assertSame('Something New', $fixture[0]->getReponses());
        self::assertSame('Something New', $fixture[0]->getReussite());
        self::assertSame('Something New', $fixture[0]->getDatePassage());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new ReponseExercice();
        $fixture->setExerciceId('Value');
        $fixture->setEnfantId('Value');
        $fixture->setScore('Value');
        $fixture->setTempsPasse('Value');
        $fixture->setReponses('Value');
        $fixture->setReussite('Value');
        $fixture->setDatePassage('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/reponse/exercice');
        self::assertSame(0, $this->reponseExerciceRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
