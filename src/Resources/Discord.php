<?php
class Discord {
	/*
	 * URL da API do discord
	 */
	private $discordURI = 'https://discordapp.com/api';
	
	/*
	 * Metodo para listar todos os membros de uma guild
	 * @param Integer $guildID
	 * @return Array
	 */
	public function listGuildMembers($guildID){
		return $this->curlRequest("{$this->discordURI}/guilds/{$guildID}/members?limit=1000", 'GET');
	}
	
	/*
	 * Metodo para pegar as informações de um membro em uma guild
	 * @param Integer $guildID
	 * @param Integer $memberID
	 * @return Array
	 */
	public function getGuildMember($guildID, $memberID){
		return $this->curlRequest("{$this->discordURI}/guilds/{$guildID}/members/{$memberID}", 'GET');
	}
	
	/*
	 * Metodo para adicionar uma ROLE em um membro
	 * @param Integer $guildID
	 * @param Integer $memberID
	 * @param Integer $roleID
	 * @return Void
	 */
	public function addGuildMemberRole($guildID, $memberID, $roleID){
		$this->curlRequest("{$this->discordURI}/guilds/{$guildID}/members/{$memberID}/roles/{$roleID}", 'PUT', http_build_query(array()));
	}
	
	/*
	 * Metodo para remover uma ROLE em um membro
	 * @param Integer $guildID
	 * @param Integer $memberID
	 * @param Integer $roleID
	 * @return Void
	 */
	public function removeGuildMemberRole($guildID, $memberID, $roleID){
		$this->curlRequest("{$this->discordURI}/guilds/{$guildID}/members/{$memberID}/roles/{$roleID}", 'DELETE', http_build_query(array()));
	}
	
	/*
	 * Metodo para adicionar uma reação a uma mensagem
	 * @param Integer $channelID
	 * @param Integer $messageID
	 * @param String $emoji
	 * @return Void
	 */
	public function createReaction($channelID, $messageID, $emoji){
		$emoji = urlencode($emoji);
		
		$this->curlRequest("{$this->discordURI}/channels/{$channelID}/messages/{$messageID}/reactions/{$emoji}/@me", 'PUT');
	}
	
	public function deleteAllReactions($channelID, $messageID){
		$this->curlRequest("{$this->discordURI}/channels/{$channelID}/messages/{$messageID}/reactions", 'DELETE');
	}
	
	/*
	 * Metodo para pegar as ultimas mensagens de um canal
	 * @param Integer $channelID
	 * @param Integer $limit
	 * @return Array
	 */
	public function getChannelMessages($channelID, $limit = 50){
		return $this->curlRequest("{$this->discordURI}/channels/{$channelID}/messages?limit={$limit}", 'GET');
	}
	
	/*
	 * Metodo para enviar uma mensagem em um canal
	 * @param String $message
	 * @param Integer $channelID
	 * @param MessageEmbed $messageEmbed
	 * @return Array
	 */
	public function createMessage($message, $channelID, $messageEmbed = ''){
		$json = new stdClass();
		
		$json->content = $message;
		$json->embed = $messageEmbed;
		
		return $this->curlRequest("{$this->discordURI}/channels/{$channelID}/messages", 'POST', json_encode($json), true);
	}
	
	/*
	 * Metodo para editar uma mensagem em um canal
	 * @param Integer $messageID
	 * @param String $newMessage
	 * @param Integer $channelID
	 * @param MessageEmbed $messageEmbed
	 * @return Void
	 */
	public function editMessage($messageID, $newMessage, $channelID, $messageEmbed = ''){
		$json = new stdClass();
		
		$json->content = $newMessage;
		$json->flags = 2;
		$json->embed = ($messageEmbed ? $messageEmbed->getEmbed() : '');
		
		$this->curlRequest("{$this->discordURI}/channels/{$channelID}/messages/{$messageID}", 'PATCH', json_encode($json), true);
	}
	
	/*
	 * Metodo para deletar uma mensagem em um canal
	 * @param Integer $channelID
	 * @param Integer $messageID
	 * @return Void
	 */
	public function deleteMessage($channelID, $messageID){
		$this->curlRequest("{$this->discordURI}/channels/{$channelID}/messages/{$messageID}", 'DELETE');
	}
	
	/*
	 * Metodo para deletar multiplas mensagem em um canal
	 * @param Integer $channelID
	 * @param Array $messageID
	 * @return Void
	 */
	public function deleteMessages($channelID, $messageID){
		$this->curlRequest("{$this->discordURI}/channels/{$channelID}/messages/bulk-delete", 'POST', json_encode($messageID), true);
	}
	
	/*
	 * Metodo para listar todos os convites de uma guild
	 * @param Integer $guildID
	 * @return Array
	 */
	public function getGuildInvites($guildID){
		return $this->curlRequest("{$this->discordURI}/guilds/{$guildID}/invites", 'GET');
	}
	
	/*
	 * Metodo para criar um convite
	 * @param Integer $channelID
	 * @param Integer $max_age
	 * @param Boolean $unique
	 * @return Array
	 */
	public function createChannelInvite($channelID, $max_age = 86400, $unique = false){
		$json = new stdClass();
		
		$json->max_age = $max_age;
		$json->unique = $unique;
		
		return $this->curlRequest("{$this->discordURI}/channels/{$channelID}/invites", 'POST', json_encode($json), true);
	}
	
	/*
	 * Metodo para pegar informação de um convite
	 * @param Integer $inviteCode
	 * @return Array
	 */
	public function getInvite($inviteCode){
		return $this->curlRequest("{$this->discordURI}/invites/{$inviteCode}", 'GET', null, true);
	}
	
	/*
	 * Metodo responsavel por processar todos os cURL
	 * @param String $url
	 * @param String $customRequest
	 * @param StdClass $postFields
	 * @param Boolean $contentType
	 * @return Array
	 */
	private function curlRequest($url, $customRequest, $postFields = null, $contentType = false){
		$httpHeader = ['Authorization: Bot ' . $this->getBotToken()];
		
		if($contentType) array_push($httpHeader, 'Content-Type: application/json');
		
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
			CURLOPT_URL            => $url,
			CURLOPT_HTTPHEADER     => $httpHeader,
			CURLOPT_CUSTOMREQUEST  => $customRequest,
			CURLOPT_POSTFIELDS	   => $postFields,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_VERBOSE        => 0, /* Mostrar DEBBUG no console */
			CURLOPT_SSL_VERIFYPEER => 0,
		));
		
		$curlResponse = curl_exec($curl);
		
		curl_close($curl);
		
		return json_decode($curlResponse, true);
	}
}