<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $exchanges = ['TWSE', 'TPEx', 'NASDAQ', 'NYSE'];
        $sectors = ['Technology', 'Financials', 'Energy', 'Healthcare', 'Consumer Discretionary', 'Industrials', 'Materials', 'Real Estate', 'Consumer Staples'];
        $exchange = fake()->randomElement($exchanges);
        $currency = in_array($exchange, ['NASDAQ', 'NYSE'], true) ? 'USD' : 'TWD';

        return [
            'symbol' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->company(),
            'exchange' => $exchange,
            'sector' => fake()->randomElement($sectors),
            'currency' => $currency,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the company is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the company is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
