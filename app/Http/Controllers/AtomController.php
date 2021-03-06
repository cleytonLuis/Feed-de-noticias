<?php

namespace Feed\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Feed\Models\Noticia;
use Feed\Http\Requests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;

class AtomController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
    
     
    public function __construct()
    {
        $this->middleware('auth');
    }
    */
    

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function gerarAtom(){

        $base_url = 'http://'.$_SERVER['HTTP_HOST'].'';

        $noticias = Noticia::where("created_at",">", Carbon::now()->subMonths(2))->orderBy('created_at','desc')->get();   

        //Iniciar documento XML
        $xmlDoc = new \DOMDocument('1.0','utf-8');
        $xmlDoc->preserveWhiteSpace = false;
        $xmlDoc->formatOutput = true;
         
        //elemento ATOM
        $xmlATOM = $xmlDoc->createElement('feed');
        $xmlATOM->setAttribute('xmlns','http://www.w3.org/2005/Atom');
        $xmlATOM = $xmlDoc->appendChild($xmlATOM);

        //Elemento <title>
        $title = $xmlDoc->createElement('title', 'Feed Provider');
        $title = $xmlATOM->appendChild($title);

        //Elemento <link>
        $link = $xmlDoc->createElement('link');
        $link->setAttribute('rel','self');
        $link->setAttribute('href', $base_url);
        $link = $xmlATOM->appendChild($link);

        //Elemento <id>
        $id = $xmlDoc->createElement('id','<![CDATA[ '.$base_url.']]>');
        $id = $xmlATOM->appendChild($id);

        
        //Elemento <author>
        $author = $xmlDoc->createElement('author');
        $author = $xmlATOM->appendChild($author);

        //Elemento <autor> -> <name>
        $nome_autor = $xmlDoc->createElement('name', 'Administrador');
        $nome_autor = $author->appendChild($nome_autor);

        foreach ($noticias as $noticia) {
            //Elemento <entry>
            $entry = $xmlDoc->createElement('entry');
            $entry = $xmlATOM->appendChild($entry);

            $linkE = $xmlDoc->createElement('link');
            $linkE->setAttribute('href',''.$base_url.$noticia->url.'');
            $linkE = $entry->appendChild($linkE);

            $titleE = $xmlDoc->createElement('title', $noticia->titulo);
            $titleE = $entry->appendChild($titleE);

            $img = '<img alt="'.$noticia->imagem_nome.'" src="'.$base_url.'/images/noticias/'.$noticia->imagem_nome.'"/>';

            $contentE = $xmlDoc->createElement('content', $img.$noticia->descricao);
            $contentE = $entry->appendChild($contentE);

            $pubDate = $xmlDoc->createElement('pubDate', $noticia->created_at);
            $pubDate = $entry->appendChild($pubDate);
        }

        header("Content-Type: text/xml");
        $xmlDoc->save("atom.xml");
        $atom = $xmlDoc->saveXML();
        Storage::disk('public')->put('atom.xml',$atom);

        print $xmlDoc->saveXML();
        header("Content-Type: text/xml");
        die();
    }
}