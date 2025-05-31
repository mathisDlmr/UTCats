<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nouvelle demande de CAT</title>
</head>
<body>
    <h2>Nouvelle demande de CAT</h2>
    
    <h3>Informations générales</h3>
    <ul>
        <li><strong>Demandeur :</strong> {{ $catRequest->user->name }} ({{ $catRequest->user->email }})</li>
        <li><strong>Date de début :</strong> {{ $catRequest->start_date->format('d/m/Y') }}</li>
        <li><strong>Date de fin :</strong> {{ $catRequest->end_date->format('d/m/Y') }}</li>
        <li><strong>Nombre de CATs :</strong> {{ $catRequest->cats_count }}</li>
    </ul>

    <h3>Responsables</h3>
    <ul>
        @foreach($catRequest->responsibles as $responsible)
            <li>{{ $responsible['name'] }}</li>
        @endforeach
    </ul>

    <h3>Articles</h3>
    <ul>
        @foreach($catRequest->articles as $article)
            <li>{{ $article['name'] }} - {{ $article['price'] }}€</li>
        @endforeach
    </ul>

    <p>
        <a href="{{ url('/admin/cat-requests/' . $catRequest->id) }}">
            Voir la demande dans l'administration
        </a>
    </p>
</body>
</html>