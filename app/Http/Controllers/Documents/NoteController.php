<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notes = Note::latest()->paginate(10);
        return view('page.documents.notes', [
            'notes' => $notes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:256',
            'paper' => 'nullable|file|max:8000|mimes:doc,docx,pdf',
            'link' => 'nullable|max:1024',
            'file' => 'required|file|max:8000|mimes:doc,docx',
        ]);

        $chars = array("+", " ", "?", "[", "]", "/", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%");

        $note = new Note;

        $note->name = $request->name;
        $note->user_id = auth()->user()->id;
        $note->sector_id = auth()->user()->sector->id;
        $note->link = $request->link;

        $source = $request->file('paper');
        if($source){
            $source_name = uniqid().$request->file('paper')->getClientOriginalName();
            $source_name = str_replace($chars, "_", $source_name);
            $upload = $source->move(public_path("/note_sources"), $source_name);
            $note->paper = $source_name;
        }

        $filename = time().$request->file->getClientOriginalName();
        $filename = str_replace($chars, "_", $filename);
        Storage::disk('local')->putFileAs(
            'files/notes/',
            $request->file,
            $filename
        );
        $note->file = $filename;

        $note->save();
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:256',
            'link' => 'nullable|max:1024',
            'paper' => 'nullable|file|max:8000|mimes:doc,docx,pdf',
            'file' => 'nullable|file|max:8000|mimes:doc,docx',
        ]);
        $chars = array("+", " ", "?", "[", "]", "/", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%");
        $notes = Note::where('id', $request->id)->first();

        $notes->name = $request->name;
        $notes->user_id = auth()->user()->id;
        $notes->sector_id = auth()->user()->sector->id;
        $notes->link = $request->link;

        $source = $request->file('paper');
        if($source){
            if($notes->paper)
                unlink(public_path('note_sources/'.$notes->paper));
            $source_name = uniqid().$request->file('paper')->getClientOriginalName();
            $source_name = str_replace($chars, "_", $source_name);
            $source->move(public_path("/note_sources"), $source_name);
            $notes->paper = $source_name;
        }

        if ($request->file) {
            $filename = time().$request->file->getClientOriginalName();
            $filename = str_replace($chars, "_", $filename);
            Storage::disk('local')->putFileAs(
                'files/notes/',
                $request->file,
                $filename
            );
            Storage::delete('files/notes/'.$digest->file);

            $notes->file = $filename;
        }

        $notes->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function sourceDownload($filename){
        return response()->download(public_path('note_sources/'.$filename));
    }

    public function noteDownload($filename){
        return response()->download(storage_path('app/files/notes/'.$filename));
    }

    public function getNoteInfo($id){
        $note = Note::with(['user'])->where('id', $id)->first();
        return response()->json(['note' => $note]);
    }
}
