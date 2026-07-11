<?php

declare(strict_types=1);

namespace BandElo\Service;

use BandElo\Config;

final class SpotifyService
{
    public function __construct(private Config $config) {}

    public function authUrl(string $state): string
    {
        return 'https://accounts.spotify.com/authorize?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->config->get('SPOTIFY_CLIENT_ID'),
            'scope' => 'user-top-read',
            'redirect_uri' => $this->config->get('SPOTIFY_REDIRECT_URI'),
            'state' => $state,
        ]);
    }

    public function token(string $code): array
    {
        return $this->requestToken(['grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => $this->config->get('SPOTIFY_REDIRECT_URI')]);
    }

    public function refresh(string $refreshToken): array
    {
        return $this->requestToken(['grant_type' => 'refresh_token', 'refresh_token' => $refreshToken]);
    }

    public function me(string $accessToken): array
    {
        return $this->api('https://api.spotify.com/v1/me', $accessToken);
    }

    public function topArtists(string $accessToken): array
    {
        $data = $this->api('https://api.spotify.com/v1/me/top/artists?limit=20', $accessToken);
        return $data['items'] ?? [];
    }

    private function requestToken(array $fields): array
    {
        $curl = curl_init('https://accounts.spotify.com/api/token');
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($this->config->get('SPOTIFY_CLIENT_ID') . ':' . $this->config->get('SPOTIFY_CLIENT_SECRET')),
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        if ($body === false || $status >= 400) {
            throw new \RuntimeException($this->spotifyErrorMessage('token', $curl, $status, is_string($body) ? $body : null));
        }

        return json_decode((string) $body, true, 512, JSON_THROW_ON_ERROR);
    }

    private function api(string $url, string $accessToken): array
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
        ]);

        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        if ($body === false || $status >= 400) {
            throw new \RuntimeException($this->spotifyErrorMessage('API', $curl, $status, is_string($body) ? $body : null));
        }

        return json_decode((string) $body, true, 512, JSON_THROW_ON_ERROR);
    }

    private function spotifyErrorMessage(string $context, \CurlHandle $curl, int $status, ?string $body): string
    {
        $curlError = curl_error($curl);
        $details = $curlError !== '' ? $curlError : $this->extractSpotifyError($body);
        $statusText = $status > 0 ? "HTTP {$status}" : 'no HTTP response';

        return sprintf('Spotify %s request failed (%s): %s', $context, $statusText, $details);
    }

    private function extractSpotifyError(?string $body): string
    {
        if ($body === null || $body === '') {
            return 'empty response body';
        }

        try {
            $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            if (isset($payload['error_description'])) {
                return (string) $payload['error_description'];
            }
            if (isset($payload['error']['message'])) {
                return (string) $payload['error']['message'];
            }
            if (isset($payload['error'])) {
                return is_string($payload['error']) ? $payload['error'] : json_encode($payload['error'], JSON_THROW_ON_ERROR);
            }
        } catch (\JsonException) {
            // Fall through to a short raw response snippet.
        }

        return substr($body, 0, 250);
    }
}
