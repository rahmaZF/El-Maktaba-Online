<?php

namespace App\Http\Controllers;

use App\Book;
use App\User;
use App\Category;

use Illuminate\Http\Request;
use App\Borrowed_Book;
use App\Favourite_Book;

class BooksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $books = Book::all();

        $categories = Category::all();
        return view('books.index', compact('books','categories'));
        // dd($categories);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

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
            'title' => 'required',
            'author' => 'required',
            'copies_number' => 'required',
            'fees_per_day' => 'required',
            'image' => 'required',
            'categories_id' => 'required',
            'description' => 'required',
        ]);

        $book = new Book();
        $book->title = $request->title;
        $book->author = $request->author;
        $book->image = $request->file('image')->store('bookImages','public');
        $book->description = $request->description;
        $book->copies_number = $request->copies_number;
        $book->fees_per_day = $request->fees_per_day;
        $book->save();
        $book->categories()->attach($request->categories_id);

        return redirect()->route('books.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function show( $bookid )
    {

        $book = Book::find($bookid);
        $comments = $book->comments()->orderByDesc('created_at')->take(5)->get();
        $relatedBooks=Book::find($bookid)->categories()->first()->books()
        ->where ('book_id', '!=',$bookid)->take(5)->get();

        // $rating = $book->ratings()->where('user_id', auth()->user()->id)->first();

        return view('books.show',compact('book','comments', 'relatedBooks'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function edit( $id)
    {
        $book = Book::find($id);
        $categories = Category::all();
        // dd($categories);
        return view('books.edit', compact('book','categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id)
    {
        $request->validate([
            'title' => 'required',
            'author' => 'required',
            'copies_number' => 'required',
            'fees_per_day' => 'required',
            'image' => 'required',
            'categories_id' => 'required',
            'description' => 'required',
        ]);
        $book = Book::find($id);
        $book->title = $request->title;
        $book->author = $request->author;
        $book->description = $request->description;
        $book->copies_number = $request->copies_number;
        $book->image = $request->file('image')->store('bookImages','public');
        $book->fees_per_day = $request->fees_per_day;
        $book->categories()->sync($request->categories_id);
        $book->save();
        return redirect()->route('books.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        Book::find($id)->delete();
        return redirect()->route('books.index');
    }


    public function borrowedBooks()
    {
        $booksInfo = Borrowed_Book::where('user_id',auth()->user()->id)->get();
        return view('user_borrowed_books',compact('booksInfo'));
    }

    public function favoriteBooks()
    {
        $booksInfo = Favourite_Book::where('user_id',auth()->user()->id)->get();
        return view('user_favorite_books',compact('booksInfo'));
    }

    public function userHomeBooks()
    {
        $categories = Category::get();
        // $books_categories = Book_Category::all();

        if(!isset($_GET['order']) && !isset($_GET['cat']))
        {
            $books = Book::paginate(3);
        }
        elseif(isset($_GET['order']) && !isset($_GET['cat']))
        {
            $books = Book::latest()->paginate(3);
            $books->withPath('?order=latest');
        }
        elseif(!isset($_GET['order']) && isset($_GET['cat']))
        {
            $books = Book::whereHas('categories', function($q)
            {
                $q->where('category_id', '=', $_GET['cat']);

            })->paginate(3);
            $books->withPath('?cat='.$_GET['cat']);

        }
        elseif(isset($_GET['order']) && isset($_GET['cat']))
        {
            $books = Book::whereHas('categories', function($q)
            {
                $q->where('category_id', '=', $_GET['cat']);

            })->latest()->paginate(3);
            $books->withPath('?cat='.$_GET['cat'].'&order=latest');
        }

        return view('home',compact('books','categories','_GET'));
    }

    public function adminBorrowedBooks()
    {
        $booksInfo = Borrowed_Book::get();
        $week1=0;
        $week2=0;
        $week3=0;
        $week4=0;
        foreach($booksInfo as $book)
        {
            if( date("m",strtotime($book->created_at)) == date('m')
                && date("d",strtotime($book->created_at)) > 0
                && date("d",strtotime($book->created_at)) <= 8  )
            {
                $fees = $book->fees_per_day * $book->number_of_days;
                $week1+=$fees;
            }
            elseif(date("m",strtotime($book->created_at)) == date('m')
                && date("d",strtotime($book->created_at)) > 8
                && date("d",strtotime($book->created_at)) <= 15  )
            {
                $fees = $book->fees_per_day * $book->number_of_days;
                $week2+=$fees;
            }
            elseif(date("m",strtotime($book->created_at)) == date('m')
            && date("d",strtotime($book->created_at)) > 15
            && date("d",strtotime($book->created_at)) <= 22  )
            {
                $fees = $book->fees_per_day * $book->number_of_days;
                $week3+=$fees;
            }
            elseif(date("m",strtotime($book->created_at)) == date('m')
            && date("d",strtotime($book->created_at)) > 22)
            {
                $fees = $book->fees_per_day * $book->number_of_days ;
                $week4+=$fees;
            }
        }
        return view('admin_borrowed_books',compact('booksInfo','week1','week2','week3','week4'));
    }

    public function saveRating(Request $request , $book_id)
    {
        $book = Book::find($request->id);

        $rating = $book->ratings()->where('user_id', auth()->user()->id)->first();

        if (is_null($rating))
        {

            $rating = new \willvincent\Rateable\Rating;


        }

        $rating->rating = $request->rate;

        $rating->user_id = auth()->user()->id;


        $book->ratings()->save($rating);


        return redirect()->back();


    }


}
