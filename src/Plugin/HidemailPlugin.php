<?php

namespace Spqr\Hidemail\Plugin;

use Pagekit\Application as App;
use Pagekit\Content\Event\ContentEvent;
use Pagekit\Event\EventSubscriberInterface;
use Sunra\PhpSimple\HtmlDomParser;


/**
 * Class HidemailPlugin
 *
 * @package Spqr\Hidemail\Plugin
 */
class HidemailPlugin implements EventSubscriberInterface
{
    
    /**
     * Content plugins callback.
     *
     * @param ContentEvent $event
     */
    public function onContentPlugins(ContentEvent $event)
    {
        $config = App::module('spqr/hidemail')->config();
        
        if ((!$config['nodes']
                || in_array(App::request()->attributes->get('_node'),
                    $config['nodes']))
            && App::request()->server->get('REQUEST_METHOD') == 'GET'
        ) {
            
            $content = $event->getContent();
            
            if ($content) {
                
                $dom = HtmlDomParser::str_get_html($content, true, true,
                    DEFAULT_TARGET_CHARSET, false, DEFAULT_BR_TEXT,
                    DEFAULT_SPAN_TEXT);
                
                foreach ($dom->find('text') as $element) {
                    if (!in_array($element->parent()->tag, ['a', 'img'])) {
                        $pattern
                            = "/(?:[a-z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/";
                        
                        preg_match_all($pattern, $content, $matches);
                        
                        foreach ($matches[0] as $email) {
                            $element->innertext = $this->replace_first($email,
                                $this->obfuscate($email), $element->innertext);
                        }
                    }
                }
                $content = $dom->save();
                $event->setContent($content);
            }
        }
    }
    
    /**
     * @param $find
     * @param $replace
     * @param $subject
     *
     * @return string
     */
    private function replace_first($find, $replace, $subject)
    {
        return implode($replace, explode($find, $subject, 2));
    }
    
    /**
     * @param $email
     *
     * @return string
     */
    function obfuscate($email)
    {
        $character_set
            = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
        
        $key         = str_shuffle($character_set);
        $cipher_text = '';
        $id          = 'e'.rand(1, 999999999);
        
        for ($i = 0; $i < strlen($email); $i += 1) {
            $cipher_text .= $key[strpos($character_set, $email[$i])];
        }
        
        $script = 'var a="'.$key.'";var b=a.split("").sort().join("");var c="'
            .$cipher_text.'";var d="";';
        
        $script .= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));';
        
        $script .= 'document.getElementById("'.$id
            .'").innerHTML="<a href=\\"mailto:"+d+"\\">"+d+"</a>"';
        
        $script = "eval(\"".str_replace(["\\", '"'], ["\\\\", '\"'], $script)
            ."\")";
        
        $script = '<script type="text/javascript">/*<![CDATA[*/'.$script
            .'/*]]>*/</script>';
        
        return '<span id="'.$id.'">[javascript protected email address]</span>'
            .$script;
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function subscribe()
    {
        return [
            'content.plugins' => ['onContentPlugins', 5],
        ];
    }
}