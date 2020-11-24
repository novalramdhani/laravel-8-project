<?php

namespace App\Http\Controllers;

use App\Models\{Category, Tag, Post};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::latest()->paginate(6);

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create', [
            'post' => new Post(),
            'categories' => Category::get(),
            'tags' => Tag::get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $attr = $this->validated();

        if(request()->file('thumbnail')) {
            $thumbnail = request()->file('thumbnail')->store('images/posts');
        } else {
            $thumbnail = null;
        }

        $attr['slug'] = Str::slug(request('title'));
        $attr['category_id'] = request('category');
        $attr['thumbnail'] = $thumbnail;

        $post = Post::create($attr);

        $post->tags()->attach(request('tags'));

        return redirect()
               ->route('posts.index')
               ->with('success', 'The new post was created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        return view('posts.edit', [
            'post' => $post,
            'categories' => Category::get(),
            'tags' => Tag::get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Post $post)
    {
        $attr = $this->validated();

        if(request()->file('thumbnail')) {
            $thumbnail = request()->file('thumbnail')->store('images/posts');
            Storage::delete($post->thumbnail);
        } else {
            $thumbnail = $post->thumbnail;
        }

        if(request()->file('thumbnail')) {
            $thumbnail = request()->file('thumbnail')->store('images/posts');
        } else {
            $thumbnail = null;
        }

        $attr['category_id'] = request('category');
        $attr['thumbnail'] = $thumbnail;

        $post->update($attr);
        $post->tags()->sync(request('tags'));

        return redirect()
               ->route('posts.index')
               ->with('success', 'The post was updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        Storage::delete($post->thumbnail);
        $post->tags()->detach();
        $post->delete();

        return redirect()
               ->route('posts.index')
               ->with('success', 'The post was deleted successfully.');
    }

    public function validated()
    {
        return request()->validate([
            'title' => ['required', 'min:10', 'max:25'],
            'content' => ['required'],
            'category' => ['required'],
            'tags' => ['array', 'required'],
            'thumbnail' => ['required', 'image', 'mimes:jpg,png,jpeg', 'max:2048']
        ]);
    }
}
