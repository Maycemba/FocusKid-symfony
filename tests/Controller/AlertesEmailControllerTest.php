<?php

namespace App\Tests\Controller;

use App\Entity\AlertesEmail;
use App\Repository\AlertesEmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AlertesEmailControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<AlertesEmail> $alertesEmailRepository */
    private EntityRepository $alertesEmailRepository;
    private string $path = '/alertes/email/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->alertesEmailRepository = $this->manager->getRepository(AlertesEmail::class);

        foreach ($this->alertesEmailRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('AlertesEmail index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'alertes_email[enfant_id]' => 'Testing',
            'alertes_email[type_alerte]' => 'Testing',
            'alertes_email[date_envoi]' => 'Testing',
            'alertes_email[email_envoye]' => 'Testing',
            'alertes_email[session_ids]' => 'Testing',
        ]);

        self::assertResponseRedirects('/alertes/email');

        self::assertSame(1, $this->alertesEmailRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new AlertesEmail();
        $fixture->setEnfantId('My Title');
        $fixture->setTypeAlerte('My Title');
        $fixture->setDateEnvoi('My Title');
        $fixture->setEmailEnvoye('My Title');
        $fixture->setSessionIds('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('AlertesEmail');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new AlertesEmail();
        $fixture->setEnfantId('Value');
        $fixture->setTypeAlerte('Value');
        $fixture->setDateEnvoi('Value');
        $fixture->setEmailEnvoye('Value');
        $fixture->setSessionIds('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'alertes_email[enfant_id]' => 'Something New',
            'alertes_email[type_alerte]' => 'Something New',
            'alertes_email[date_envoi]' => 'Something New',
            'alertes_email[email_envoye]' => 'Something New',
            'alertes_email[session_ids]' => 'Something New',
        ]);

        self::assertResponseRedirects('/alertes/email');

        $fixture = $this->alertesEmailRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getEnfantId());
        self::assertSame('Something New', $fixture[0]->getTypeAlerte());
        self::assertSame('Something New', $fixture[0]->getDateEnvoi());
        self::assertSame('Something New', $fixture[0]->getEmailEnvoye());
        self::assertSame('Something New', $fixture[0]->getSessionIds());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new AlertesEmail();
        $fixture->setEnfantId('Value');
        $fixture->setTypeAlerte('Value');
        $fixture->setDateEnvoi('Value');
        $fixture->setEmailEnvoye('Value');
        $fixture->setSessionIds('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/alertes/email');
        self::assertSame(0, $this->alertesEmailRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
