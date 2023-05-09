<?php

namespace Tests\Resources;

use DiscordPHP\Resources\MessageEmbed;
use PHPUnit\Framework\TestCase;

class MessageEmbedTest extends TestCase
{
    public function testSetColor()
    {
        $embed = new MessageEmbed();
        $embed->setColor("#FF0000");
        $this->assertEquals(16711680, $embed->getEmbed()->color);
    }

    public function testSetTitle()
    {
        $embed = new MessageEmbed();
        $embed->setTitle("Test Title");
        $this->assertEquals("Test Title", $embed->getEmbed()->title);
    }

    public function testSetURL()
    {
        $embed = new MessageEmbed();
        $embed->setURL("https://example.com");
        $this->assertEquals("https://example.com", $embed->getEmbed()->url);
    }

    public function testSetAuthor()
    {
        $embed = new MessageEmbed();
        $embed->setAuthor("Test Author", "https://example.com/icon.png", "https://example.com");
        $this->assertEquals("Test Author", $embed->getEmbed()->author->name);
        $this->assertEquals("https://example.com/icon.png", $embed->getEmbed()->author->icon_url);
        $this->assertEquals("https://example.com", $embed->getEmbed()->author->url);
    }

    public function testSetDescription()
    {
        $embed = new MessageEmbed();
        $embed->setDescription("Test Description");
        $this->assertEquals("Test Description", $embed->getEmbed()->description);
    }

    public function testSetThumbnail()
    {
        $embed = new MessageEmbed();
        $embed->setThumbnail("https://example.com/thumbnail.png");
        $this->assertEquals("https://example.com/thumbnail.png", $embed->getEmbed()->thumbnail->url);
    }

    public function testAddFields()
    {
        $embed = new MessageEmbed();
        $fields = json_decode(json_encode([
            [
                "name" => "Test Field 1",
                "value" => "Test Field Value 1",
                "inline" => false
            ],
            [
                "name" => "Test Field 2",
                "value" => "Test Field Value 2",
                "inline" => true
            ]
        ]));

        $embed->addFields($fields);
        $this->assertEquals($fields, $embed->getEmbed()->fields);
    }

    public function testAddField()
    {
        $embed = new MessageEmbed();
        $embed->addField("Test Field", "Test Field Value", true);
        $field = $embed->getEmbed()->fields[0];
        $this->assertEquals("Test Field", $field->name);
        $this->assertEquals("Test Field Value", $field->value);
        $this->assertEquals(true, $field->inline);
    }

    public function testSetImage()
    {
        $embed = new MessageEmbed();
        $embed->setImage("https://example.com/image.png");
        $this->assertEquals("https://example.com/image.png", $embed->getEmbed()->thumbnail->url);
    }

    public function testSetTimestamp()
    {
        $embed = new MessageEmbed();
        $embed->setTimestamp();
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{4}$/', $embed->getEmbed()->timestamp);
    }

    public function testSetFooter()
    {
        $embed = new MessageEmbed();
        $embed->setFooter("Footer Title", "https://example.com/footer-icon.png");

        $this->assertEquals("Footer Title", $embed->getEmbed()->footer->text);
        $this->assertEquals("https://example.com/footer-icon.png", $embed->getEmbed()->footer->icon_url);
    }
}
