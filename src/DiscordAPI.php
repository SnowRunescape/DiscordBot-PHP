<?php

namespace DiscordPHP;

use DiscordPHP\Resources\MessageEmbed;

class DiscordAPI
{
    private Discord $discord;

    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    /*
     * URL da API do discord
     */
    private $discordURI = "https://discordapp.com/api";

    /*
     * Metodo para listar todos os membros de uma guild
     * @param Integer $guildId
     * @return Array
     */
    public function listGuildMembers($guildId)
    {
        return $this->curlRequest("/guilds/{$guildId}/members?limit=1000");
    }

    /*
     * Metodo para pegar as informações de um membro em uma guild
     * @param Integer $guildId
     * @param Integer $memberId
     * @return Array
     */
    public function getGuildMember($guildId, $memberId)
    {
        return $this->curlRequest("/guilds/{$guildId}/members/{$memberId}");
    }

    /*
     * Metodo para adicionar uma ROLE em um membro
     * @param Integer $guildId
     * @param Integer $memberId
     * @param Integer $roleId
     * @return Void
     */
    public function addGuildMemberRole($guildId, $memberId, $roleId)
    {
        $this->curlRequest(
            "/guilds/{$guildId}/members/{$memberId}/roles/{$roleId}",
            "PUT",
            http_build_query([])
        );
    }

    /*
     * Metodo para remover uma ROLE em um membro
     * @param Integer $guildId
     * @param Integer $memberId
     * @param Integer $roleId
     * @return Void
     */
    public function removeGuildMemberRole($guildId, $memberId, $roleId)
    {
        $this->curlRequest(
            "/guilds/{$guildId}/members/{$memberId}/roles/{$roleId}",
            "DELETE",
            http_build_query([])
        );
    }

    /*
     * Metodo para adicionar uma reação a uma mensagem
     * @param Integer $channelId
     * @param Integer $messageId
     * @param String $emoji
     * @return Void
     */
    public function createReaction($channelId, $messageId, $emoji)
    {
        $emoji = urlencode($emoji);

        $this->curlRequest("/channels/{$channelId}/messages/{$messageId}/reactions/{$emoji}/@me", "PUT");
    }

    public function deleteAllReactions($channelId, $messageId)
    {
        $this->curlRequest("/channels/{$channelId}/messages/{$messageId}/reactions", "DELETE");
    }

    /*
     * Metodo para pegar as ultimas mensagens de um canal
     * @param Integer $channelId
     * @param Integer $limit
     * @return Array
     */
    public function getChannelMessages($channelId, $limit = 50)
    {
        return $this->curlRequest("/channels/{$channelId}/messages?limit={$limit}");
    }

    /*
     * Metodo para enviar uma mensagem em um canal
     * @param String $message
     * @param Integer $channelId
     * @param MessageEmbed $messageEmbed
     * @return Array
     */
    public function createMessage($message, $channelId, $messageEmbed = null)
    {
        $json = new \stdClass();

        $json->content = $message;

        if ($messageEmbed) {
            $json->embeds = [$messageEmbed];
        }

        return $this->curlRequest("/channels/{$channelId}/messages", "POST", json_encode($json), true);
    }

    /*
     * Metodo para editar uma mensagem em um canal
     * @param Integer $messageId
     * @param String $newMessage
     * @param Integer $channelId
     * @param MessageEmbed $messageEmbed
     * @return Void
     */
    public function editMessage(int $messageId, string $newMessage, int $channelId, MessageEmbed $messageEmbed = null)
    {
        $json = new \stdClass();

        $json->content = $newMessage;
        $json->flags = 2;

        if ($messageEmbed) {
            $json->embeds = [$messageEmbed];
        }

        $this->curlRequest("/channels/{$channelId}/messages/{$messageId}", "PATCH", json_encode($json), true);
    }

    /*
     * Metodo para deletar uma mensagem em um canal
     * @param Integer $channelId
     * @param Integer $messageId
     * @return Void
     */
    public function deleteMessage($channelId, $messageId)
    {
        $this->curlRequest("/channels/{$channelId}/messages/{$messageId}", "DELETE");
    }

    /*
     * Metodo para deletar multiplas mensagem em um canal
     * @param Integer $channelId
     * @param Array $messageId
     * @return Void
     */
    public function deleteMessages($channelId, $messageId)
    {
        $this->curlRequest("/channels/{$channelId}/messages/bulk-delete", "POST", json_encode($messageId), true);
    }

    /*
     * Metodo para listar todos os convites de uma guild
     * @param Integer $guildId
     * @return Array
     */
    public function getGuildInvites($guildId)
    {
        return $this->curlRequest("/guilds/{$guildId}/invites");
    }

    /*
     * Metodo para criar um convite
     * @param Integer $channelId
     * @param Integer $max_age
     * @param Boolean $unique
     * @return Array
     */
    public function createChannelInvite($channelId, $max_age = 86400, $unique = false)
    {
        $json = new \stdClass();

        $json->max_age = $max_age;
        $json->unique = $unique;

        return $this->curlRequest("/channels/{$channelId}/invites", "POST", json_encode($json), true);
    }

    /*
     * Metodo para pegar informação de um convite
     * @param Integer $inviteCode
     * @return Array
     */
    public function getInvite($inviteCode)
    {
        return $this->curlRequest("/invites/{$inviteCode}", "GET", null, true);
    }

    /*
     * Metodo responsavel por processar todos os cURL
     * @param String $url
     * @param String $customRequest
     * @param StdClass $postFields
     * @param Boolean $contentType
     * @return Array
     */
    private function curlRequest($url, $customRequest = "GET", $postFields = null, $contentType = false)
    {
        $httpHeader = [
            "Authorization: Bot {$this->discord->getBotToken()}"
        ];

        if ($contentType) {
            $httpHeader[] = "Content-Type: application/json";
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => "{$this->discordURI}/{$url}",
            CURLOPT_HTTPHEADER     => $httpHeader,
            CURLOPT_CUSTOMREQUEST  => $customRequest,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_VERBOSE        => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ]);

        $curlResponse = curl_exec($curl);

        curl_close($curl);

        return json_decode($curlResponse, true);
    }
}
