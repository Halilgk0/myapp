<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeviceLocation>
 */
class DeviceLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Base coordinates for Istanbul
        $baseLat = 41.0082;
        $baseLng = 28.9784;
        
        // Generate random point within 10km of the base coordinates
        $lat = $this->faker->latitude(
            $baseLat - 0.09,  // ~10km in degrees latitude
            $baseLat + 0.09
        );
        
        $lng = $this->faker->longitude(
            $baseLng - 0.11,  // ~10km in degrees longitude (adjusted for latitude)
            $baseLng + 0.11
        );
        
        return [
            'device_id' => Device::factory(),
            'latitude' => $lat,
            'longitude' => $lng,
            'speed' => $this->faker->randomFloat(2, 0, 120),
            'heading' => $this->faker->numberBetween(0, 359),
            'altitude' => $this->faker->randomFloat(2, 0, 1000),
            'accuracy' => $this->faker->randomFloat(2, 1, 50),
            'recorded_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }
    
    /**
     * Indicate that the location is recent (last 5 minutes).
     */
    public function recent()
    {
        return $this->state(function (array $attributes) {
            return [
                'recorded_at' => now()->subMinutes(rand(0, 5)),
                'created_at' => now()->subMinutes(rand(0, 5)),
                'updated_at' => now(),
            ];
        });
    }
}
