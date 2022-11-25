<?php

namespace App\Http\Controllers;

use App\Models\Digest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class DigestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $digests = Digest::latest()->paginate(10);
        $users = User::select(['id', 'name'])->get();

        return view('page.documents.digests', [
            'digests' => $digests,
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:256',
            'paper' => 'nullable|file|max:8000|mimes:doc,docx,pdf',
            'link' => 'nullable|max:1024',
            'file' => 'required|file|max:8000|mimes:doc,docx',
        ]);

        $chars = array("+", " ", "?", "[", "]", "/", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%");

        $digest = new Digest;

        $digest->name = $request->name;
        $digest->user_id = auth()->user()->id;
        $digest->sector_id = auth()->user()->sector->id;
        // $digest->paper = $request->paper;
        $digest->link = $request->link;

        $source = $request->file('paper');
        if($source){
            $source_name = uniqid().$request->file('paper')->getClientOriginalName();
            $source_name = str_replace($chars, "_", $source_name);
            $upload = $source->move(public_path("/digest_sources"), $source_name);
            $digest->paper = $source_name;
        }

        $filename = time().$request->file->getClientOriginalName();
        $filename = str_replace($chars, "_", $filename);
        Storage::disk('local')->putFileAs(
            'files/digests/',
            $request->file,
            $filename
        );
        $digest->file = $filename;

        $digest->save();
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:256',
            'link' => 'nullable|max:1024',
            'paper' => 'nullable|file|max:8000|mimes:doc,docx,pdf',
            'file' => 'nullable|file|max:8000|mimes:doc,docx',
        ]);
        $chars = array("+", " ", "?", "[", "]", "/", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%");
        $digest = Digest::where('id', $request->id)->first();

        $digest->name = $request->name;
        $digest->user_id = auth()->user()->id;
        $digest->sector_id = auth()->user()->sector->id;
        $digest->link = $request->link;

        $source = $request->file('paper');
        if($source){
            if($digest->paper)
                unlink(public_path('digest_sources/'.$digest->paper));
            $source_name = uniqid().$request->file('paper')->getClientOriginalName();
            $source_name = str_replace($chars, "_", $source_name);
            $source->move(public_path("/digest_sources"), $source_name);
            $digest->paper = $source_name;
        }

        if ($request->file) {
            $filename = time().$request->file->getClientOriginalName();
            $filename = str_replace($chars, "_", $filename);
            Storage::disk('local')->putFileAs(
                'files/digests/',
                $request->file,
                $filename
            );
            Storage::delete('files/digests/'.$digest->file);

            $digest->file = $filename;
        }

        $digest->save();
    }

    public function destroy($id)
    {
        $digest = Digest::where('id', $id)->first();
        $digest->delete();

        Storage::delete('files/digests/'.$digest->file);

        return back();
    }

    public function paperDownload($filename){
        return response()->download(public_path('digest_sources/'.$filename));
    }

    public function getDigestInfo($id){
        $digest = Digest::with(['user'])->where('id', $id)->first();
        return response()->json(['digest' => $digest]);
    }

    public function digestDownload($filename){
        return response()->download(storage_path('app/files/digests/'.$filename));
    }

    public function uploadTest(Request $request){
        $request->validate([
            'file' => 'file|max:6000|mimes:doc,docx'
        ]);

        $input = $request->file('file');

        $filename = uniqid().$input->getClientOriginalName();
        $upload = $input->move(public_path("/tmp_digests"), $filename);

        $file = fopen(public_path("tmp_digests/".$filename), 'r');

        $response = Http::attach(
            'attachment', $file
        )->post('http://192.168.1.60:8888/', [
            'name' => auth()->user()->name
        ]);

        copy($response->json(), public_path("tmp_digests/".$filename));
        // unlink(public_path("tmp_digests/".$filename));

        return response()->download(public_path("tmp_digests/".$filename));
    }

    public function formatter(){
        return view('page.documents.digest_formatter');
    }
}
