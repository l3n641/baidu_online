@extends('common')
@section('sidebar')
    <div class="row">
        <div class="col-lg-6">
            <form action="" method="post">
                @csrf
                <div class="input-group">
                    <input type="text" class="form-control" name="site" id="site" placeholder="Search for...">
                    <span class="input-group-btn">
                  <button class="btn btn-default" type="submit" id="search">Go!</button>
                </span>
                </div>
            </form>

        </div>
    </div>
@endsection