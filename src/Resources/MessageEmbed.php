<?php

namespace DiscordPHP\Resources;

class MessageEmbed
{
    /*
     * Embed
     */
    private $embed = [];

    /*
     * Metodo para obter o Embed
     */
    public function getEmbed()
    {
        return json_decode(json_encode($this->embed));
    }

    /*
     * Metodo para definir a cor
     * @param Hexadecimal $color
     * @return Void
     */
    public function setColor(string $color)
    {
        $color = ltrim($color, "#");

        if (ctype_xdigit($color)) {
            $this->embed["color"] = hexdec($color);
        }
    }

    /*
     * Metodo para definir o titulo
     * @param String $title
     * @return Void
     */
    public function setTitle(string $title)
    {
        $this->embed["title"] = $title;
    }

    /*
     * Metodo para definir a URL do titulo
     * @param String $url
     * @return Void
     */
    public function setURL(string $url)
    {
        $this->embed["url"] = $url;
    }

    /*
     * Metodo para definir um autor
     * @param String $name
     * @param String $icon_url
     * @param String $url
     * @return Void
     */
    public function setAuthor(string $name, string $iconUrl, string $url = "")
    {
        $this->embed["author"]["name"] = $name;
        $this->embed["author"]["icon_url"] = $iconUrl;
        $this->embed["author"]["url"] = $url;
    }

    /*
     * Metodo para definir uma descrição
     * @param String $description
     * @return Void
     */
    public function setDescription(string $description)
    {
        $this->embed["description"] = $description;
    }

    /*
     * Metodo para definir um Thumbnail
     * @param String $thumbnail
     * @return Void
     */
    public function setThumbnail(string $thumbnail)
    {
        $this->embed["thumbnail"]["url"] = $thumbnail;
    }

    /*
     * Metodo para definir multiplos fields
     * @param Array $fields
     * @return Void
     */
    public function addFields(array $fields)
    {
        $this->embed["fields"] = $fields;
    }

    /*
     * Metodo para definir um field
     * @param String $name
     * @param String $value
     * @param Boolean $inline
     * @return Void
     */
    public function addField(string $name, string $value, bool $inline = false)
    {
        $this->embed["fields"] = [
            0 => [
                "name" => $name,
                "value" => $value,
                "inline" => $inline
            ]
        ];
    }

    /*
     * Metodo para definir uma imagem no footer
     * @param String $image_url
     * @return Void
     */
    public function setImage(string $imageUrl)
    {
        $this->embed["thumbnail"]["url"] = $imageUrl;
    }

    /*
     * Metodo para habilitar o Timestamp
     * @return Void
     */
    public function setTimestamp()
    {
        $this->embed["timestamp"] = date(\DateTime::ISO8601);
    }

    /*
     * Metodo para definir o footer
     * @param String $title
     * @param String $icon_url
     * @return Void
     */
    public function setFooter(string $title, string $iconUrl)
    {
        $this->embed["footer"]["text"] = $title;
        $this->embed["footer"]["icon_url"] = $iconUrl;
    }
}
