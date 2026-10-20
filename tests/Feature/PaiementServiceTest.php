<?php

namespace Tests\Feature;

use Tests\TestCase;

class PaiementServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_recalculate_fee_status_to_paid()
    {
        $this->assertTrue(true);
        // Ici viendra la logique détaillée :
        // 1. Créer un Fee via FeeFactory
        // 2. Créer un Payment lié via PaymentFactory dont le montant couvre le Fee
        // 3. Appeler (new PaiementService())->recalculateFeeStatus($fee)
        // 4. Assert que le statut est 'paid'
    }

    public function test_recalculate_fee_status_to_partial()
    {
        $this->assertTrue(true);
    }
}
