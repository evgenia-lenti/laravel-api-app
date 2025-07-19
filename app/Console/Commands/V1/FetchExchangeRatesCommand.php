<?php

namespace App\Console\Commands\V1;

use App\Services\V1\ExchangeRateService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class FetchExchangeRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-rates:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch exchange rates from ECB and store them in the database';

    public function __construct(
        private ExchangeRateService $exchangeRateService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching exchange rates from ECB...');

        try {
            $rates = $this->exchangeRateService->fetchAndStoreRates();

            $this->info('Successfully fetched and stored ' . count($rates) . ' exchange rates.');

            return CommandAlias::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to fetch exchange rates: ' . $e->getMessage());

            return CommandAlias::FAILURE;
        }
    }
}
