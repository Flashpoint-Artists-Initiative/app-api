<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Event;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ArtProjectVotingRule implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    protected array $data = [];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $totalVotes = array_sum($this->data['data']['votes'] ?? []);
        $maxVotes = Event::getCurrentEvent()->votesPerUser ?? 0;

        if ($totalVotes > $maxVotes) {
            $fail('You cannot submit more than ' . $maxVotes . ' votes.');

            return;
        }

        if ($totalVotes < $maxVotes) {
            $fail('Use all your votes before submitting to support your favorite projects!');

            return;
        }
    }

    /** @param array<string, mixed> $data */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
