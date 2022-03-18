<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles = Article::all();
        $categories = Category::select(['id', 'name'])->get();
        $users = User::select(['id', 'name'])->get();

        return view('page.documents.index', [
            'articles' => $articles,
            'categories' => $categories,
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:256',
            'description' => 'required|max:1024',
            'link' => 'nullable|max:1024',
            'file' => 'required|file|max:8000|mimes:doc,docx,pdf',
        ]);

        $article = new Article;

        $article->name = $request->name;
        $article->user_id = auth()->user()->id;
        $article->sector_id = auth()->user()->sector->id;
        $article->category_id = $request->category_id;
        $article->description = $request->description;
        $article->link = $request->link;

        $filename = time().$request->file->getClientOriginalName();
        Storage::disk('local')->putFileAs(
            'files/articles/',
            $request->file,
            $filename
        );
        $article->file = $filename;

        $article->save();
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:256',
            'description' => 'required|max:1024',
            'link' => 'nullable|max:1024',
            'file' => 'file|max:8000|mimes:doc,docx,pdf',
        ]);

        $article = Article::where('id', $request->id)->first();
        $user = User::with('sector')->where('id', $request->user_id)->first();

        $article->name = $request->name;
        $article->user_id = $request->user_id;
        $article->sector_id = $user->sector->id;
        $article->category_id = $request->category_id;
        $article->description = $request->description;
        $article->link = $request->link;

        if ($request->file) {
            Storage::delete('/files/articles/'.$article->file);
            $filename = time().$request->file->getClientOriginalName();
            Storage::disk('local')->putFileAs(
                'files/articles/',
                $request->file,
                $filename
            );
            $article->file = $filename;
        }

        $article->save();
    }

    public function destroy($id)
    {
        //
    }
}
