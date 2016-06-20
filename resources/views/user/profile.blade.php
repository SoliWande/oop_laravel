@extends('layouts.master')

@section('content')
    @foreach ($user as $key=>$oneUser)
        {{$key.':'.$oneUser}} <br>
    @endforeach
@stop