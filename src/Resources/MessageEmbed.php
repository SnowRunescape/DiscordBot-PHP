<?php
class MessageEmbed {
	/*
	 * Embed
	 */
	private $embed = [];
	
	/*
	 * Metodo para obter o Embed
	 */
	public function getEmbed(){
		return json_decode(json_encode($this->embed));
	}
	
	/*
	 * Metodo para definir a cor
	 * @param Hexadecimal $color
	 * @return Void
	 */
	public function setColor($color){
		$this->embed['color'] = hexdec($color);
	}
	
	/*
	 * Metodo para definir o titulo
	 * @param String $title
	 * @return Void
	 */
	public function setTitle($title){
		$this->embed['title'] = $title;
	}
	
	/*
	 * Metodo para definir a URL do titulo
	 * @param String $url
	 * @return Void
	 */
	public function setURL($url){
		$this->embed['url'] = $url;
	}
	
	/*
	 * Metodo para definir um autor
	 * @param String $name
	 * @param String $icon_url
	 * @param String $url
	 * @return Void
	 */
	public function setAuthor($name, $icon_url, $url = 'https://github.com/SnowRunescape/DiscordBot-PHP'){
		$this->embed['author']['name'] = $name;
		$this->embed['author']['icon_url'] = $icon_url;
		$this->embed['author']['url'] = $url;
	}
	
	/*
	 * Metodo para definir uma descrição
	 * @param String $description
	 * @return Void
	 */
	public function setDescription($description){
		$this->embed['description'] = $description;
	}
	
	/*
	 * Metodo para definir um Thumbnail
	 * @param String $thumbnail
	 * @return Void
	 */
	public function setThumbnail($thumbnail){
		$this->embed['thumbnail']['url'] = $thumbnail;
	}
	
	/*
	 * Metodo para definir multiplos fields
	 * @param Array $fields
	 * @return Void
	 */
	public function addFields($fields){
		$this->embed['fields'] = $fields;
	}
	
	/*
	 * Metodo para definir um field
	 * @param String $name
	 * @param String $value
	 * @param Boolean $inline
	 * @return Void
	 */
	public function addField($name, $value, $inline = false){
		$this->embed['fields'] = [0 => ['name' => $name, 'value' => $value, 'inline' => $inline]];
	}
	
	/*
	 * Metodo para definir uma imagem no footer
	 * @param String $image_url
	 * @return Void
	 */
	public function setImage($image_url){
		$this->embed['thumbnail']['url'] = $image_url;
	}
	
	/*
	 * Metodo para habilitar o Timestamp
	 * @return Void
	 */
	public function setTimestamp(){
		$this->embed['timestamp'] = date(DateTime::ISO8601);
	}
	
	/*
	 * Metodo para definir o footer
	 * @param String $title
	 * @param String $icon_url
	 * @return Void
	 */
	public function setFooter($title, $icon_url){
		$this->embed['footer']['text'] = $title;
		$this->embed['footer']['icon_url'] = $icon_url;
	}
}