@extends('layouts.app')
@section('content')
<main class="pt-90">
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
        <h2 class="page-title">Addresses</h2>
        <div class="row">
            <div class="col-lg-2">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content my-account__address">
                    <div class="col-6">
                        The following addresses will be used on the checkout page by default.
                    </div>
                    <br>
                    <div class="my-account__address-list row">
                        <h3>Shipping Address</h3>

                        <div class="my-account__address-item col-md-6">
                            <div class="my-account__address-item__title">
                                <h5>{{ $address->name ?? '-' }} <i class="fa fa-check-circle text-success"></i></h5>
                                <a href="{{route('user.address.edit')}}">Edit</a>
                            </div>
                            <div class="my-account__address-item__detail">
                                <p>{{ $address->locality ?? '-' }}</p>
                                <p>{{ $address->address ?? '-' }}</p>
                                <p>{{ $address->city ?? '-' }}</p>
                                <p>{{ $address->landmark ?? '-' }}</p>
                                <p>{{ $address->zip ?? '-' }}</p>
                                <br>
                                <p>{{ $address->phone ?? '-' }}</p>
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
