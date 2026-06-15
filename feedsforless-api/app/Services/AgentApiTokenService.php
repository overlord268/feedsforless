<?php

namespace App\Services;

use App\Models\AgentApiToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgentApiTokenService
{
    private const TOKEN_LENGTH = 48;

    /**
     * @return array{token: AgentApiToken, plain_token: string}
     */
    public function create(string $name, ?int $createdByUserId = null, ?int $rotatedFromId = null): array
    {
        $plainToken = Str::random(self::TOKEN_LENGTH);

        $token = AgentApiToken::create([
            'name' => $name,
            'token_prefix' => AgentApiToken::extractPrefix($plainToken),
            'token_hash' => AgentApiToken::hashPlainToken($plainToken),
            'created_by_user_id' => $createdByUserId,
            'rotated_from_id' => $rotatedFromId,
        ]);

        return [
            'token' => $token,
            'plain_token' => $plainToken,
        ];
    }

    public function revoke(AgentApiToken $token): void
    {
        if ($token->revoked_at !== null) {
            return;
        }

        $token->forceFill(['revoked_at' => now()])->save();
    }

    /**
     * @return array{token: AgentApiToken, plain_token: string}
     */
    public function rotate(AgentApiToken $token, ?int $createdByUserId = null): array
    {
        return DB::transaction(function () use ($token, $createdByUserId) {
            $this->revoke($token);

            return $this->create(
                $token->name,
                $createdByUserId,
                $token->id
            );
        });
    }

    public function findActiveByPlainToken(string $plainToken): ?AgentApiToken
    {
        if (strlen($plainToken) < 32) {
            return null;
        }

        $hash = AgentApiToken::hashPlainToken($plainToken);

        $token = AgentApiToken::query()
            ->where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->first();

        if ($token === null || ! $token->matches($plainToken)) {
            return null;
        }

        return $token;
    }
}
