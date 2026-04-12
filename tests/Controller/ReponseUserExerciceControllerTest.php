<?php

namespace App\Tests\Controller;

use App\Entity\ReponseUserExercice;
use App\Repository\ReponseUserExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ReponseUserExerciceControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<ReponseUserExercice> $reponseUserExerciceRepository */
    private EntityRepository $reponseUserExerciceRepository;
    private string $path = '/reponse/user/exercice/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->reponseUserExerciceRepository = $this->manager->getRepository(ReponseUserExercice::class);

        foreach ($this->reponseUserExerciceRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ReponseUserExercice index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'reponse_user_exercice[reponse]' => 'Testing',
            'reponse_user_exercice[est_correcte]' => 'Testing',
            'reponse_user_exercice[temps_reponse]' => 'Testing',
            'reponse_user_exercice[date_reponse]' => 'Testing',
            'reponse_user_exercice[utilisateur]' => 'Testing',
            'reponse_user_exercice[exercice]' => 'Testing',
            'reponse_user_exercice[questionExercice]' => 'Testing',
        ]);

        self::assertResponseRedirects('/reponse/user/exercice');

        self::assertSame(1, $this->reponseUserExerciceRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new ReponseUserExercice();
        $fixture->setReponse('My Title');
        $fixture->setEstCorrecte('My Title');
        $fixture->setTempsReponse('My Title');
        $fixture->setDateReponse('My Title');
        $fixture->setUtilisateur('My Title');
        $fixture->setExercice('My Title');
        $fixture->setQuestionExercice('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('ReponseUserExercice');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new ReponseUserExercice();
        $fixture->setReponse('Value');
        $fixture->setEstCorrecte('Value');
        $fixture->setTempsReponse('Value');
        $fixture->setDateReponse('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setExercice('Value');
        $fixture->setQuestionExercice('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'reponse_user_exercice[reponse]' => 'Something New',
            'reponse_user_exercice[est_correcte]' => 'Something New',
            'reponse_user_exercice[temps_reponse]' => 'Something New',
            'reponse_user_exercice[date_reponse]' => 'Something New',
            'reponse_user_exercice[utilisateur]' => 'Something New',
            'reponse_user_exercice[exercice]' => 'Something New',
            'reponse_user_exercice[questionExercice]' => 'Something New',
        ]);

        self::assertResponseRedirects('/reponse/user/exercice');

        $fixture = $this->reponseUserExerciceRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getReponse());
        self::assertSame('Something New', $fixture[0]->getEstCorrecte());
        self::assertSame('Something New', $fixture[0]->getTempsReponse());
        self::assertSame('Something New', $fixture[0]->getDateReponse());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());
        self::assertSame('Something New', $fixture[0]->getExercice());
        self::assertSame('Something New', $fixture[0]->getQuestionExercice());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new ReponseUserExercice();
        $fixture->setReponse('Value');
        $fixture->setEstCorrecte('Value');
        $fixture->setTempsReponse('Value');
        $fixture->setDateReponse('Value');
        $fixture->setUtilisateur('Value');
        $fixture->setExercice('Value');
        $fixture->setQuestionExercice('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/reponse/user/exercice');
        self::assertSame(0, $this->reponseUserExerciceRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
