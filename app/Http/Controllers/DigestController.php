<?php

namespace App\Http\Controllers;

use App\Models\Digest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DigestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $digests = Digest::all();
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
            'link' => 'nullable|max:1024',
            'file' => 'required|file|max:8000|mimes:doc,docx,pdf',
        ]);

        $digest = new Digest;

        $digest->name = $request->name;
        $digest->user_id = auth()->user()->id;
        $digest->sector_id = auth()->user()->sector->id;
        $digest->link = $request->link;

        $filename = time().$request->file->getClientOriginalName();
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
            'description' => 'required|max:1024',
            'link' => 'nullable|max:1024',
            'file' => 'file|max:8000|mimes:doc,docx,pdf',
        ]);

        $digest = Digest::where('id', $request->id)->first();
        $user = User::with('sector')->where('id', $request->user_id)->first();

        $digest->name = $request->name;
        $digest->user_id = $request->user_id;
        $digest->sector_id = $user->sector->id;
        $digest->category_id = $request->category_id;
        $digest->description = $request->description;
        $digest->link = $request->link;

        if ($request->file) {
            Storage::delete('/files/articles/'.$digest->file);
            $filename = time().$request->file->getClientOriginalName();
            Storage::disk('local')->putFileAs(
                'files/articles/',
                $request->file,
                $filename
            );
            $digest->file = $filename;
        }

        $digest->save();
    }

    public function destroy($id)
    {
        $digest = Digest::where('id', $id)->first();
        $digest->delete();

        return back();
    }
}
