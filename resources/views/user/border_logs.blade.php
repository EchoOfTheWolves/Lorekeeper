@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Border Logs
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Border Logs' => $user->url . '/border-logs']) !!}

    <h1>
        {!! $user->displayName !!}'s Border Logs
    </h1>

    {!! $logs->render() !!}
    <div class="row ml-md-2 mb-4">
        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
            <div class="col-6 col-md-2 font-weight-bold">Sender</div>
            <div class="col-6 col-md-2 font-weight-bold">Recipient</div>
            <div class="col-6 col-md-2 font-weight-bold">Border</div>
            <div class="col-6 col-md-4 font-weight-bold">Log</div>
            <div class="col-6 col-md-2 font-weight-bold">Date</div>
        </div>
        @foreach ($logs as $log)
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                <div class="col-6 col-md-2">
                    <i
                        class="btn py-1 m-0 px-2 btn-{{ $log->recipient_id == $user->id ? 'success' : 'danger' }} fas {{ $log->recipient_id == $user->id && $log->recipient_type == $user->logType ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-2"></i>
                    {!! $log->sender ? $log->sender->displayName : '' !!}
                </div>
                <div class="col-6 col-md-2">{!! $log->recipient ? $log->recipient->displayName : '' !!}</div>
                <div class="col-6 col-md-2">{!! $log->border ? $log->border->displayName : '(Deleted Border)' !!}</div>
                <div class="col-6 col-md-4">{!! $log->log !!}</div>
                <div class="col-6 col-md-2">{!! pretty_date($log->created_at) !!}</div>
            </div>
        @endforeach
    </div>
    {!! $logs->render() !!}
@endsection
