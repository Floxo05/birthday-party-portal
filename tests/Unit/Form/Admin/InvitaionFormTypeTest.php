<?php
declare(strict_types=1);

namespace App\Tests\Unit\Form\Admin;

use App\Entity\PartyMember;
use App\Form\Admin\InvitationFormType;
use App\Form\Model\InvitationFormModel;
use App\Service\PartyMember\PartyMemberRoleTranslator\PartyMemberRoleTranslatorInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class InvitaionFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        // ⛳️ Erzeuge einen Mock des Translators
        $roleTranslator = $this->createMock(PartyMemberRoleTranslatorInterface::class);
        $roleTranslator->method('translate')->willReturnMap([
            [PartyMember::ROLE_GUEST, 'GUEST_LABEL'],
            [PartyMember::ROLE_HOST, 'HOST_LABEL'],
        ]);

        // ⛳️ Gib den FormType mit der Dependency zurück
        $type = new InvitationFormType($roleTranslator);

        return [
            new PreloadedExtension([$type], []),
        ];
    }

    public function testFormSubmission(): void
    {
        $formData = [
            'role' => 'Guest',
            'maxUses' => 3,
            'expiresAt' => '2025-05-01T12:00:00',
        ];

        $form = $this->factory->create(InvitationFormType::class, null, [
            'party' => null,
        ]);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized(), 'Form is not synchronized');
        $this->assertTrue($form->isValid(), 'Form is invalid');

        /** @var InvitationFormModel $result */
        $result = $form->getData();
        $this->assertSame('Guest', $result->role);
        $this->assertSame(3, $result->maxUses);
        $this->assertEquals(new \DateTime('2025-05-01T12:00:00'), $result->expiresAt);
    }
}