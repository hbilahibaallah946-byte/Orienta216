<!DOCTYPE html>

<html>
<head>
    <title>Choix spécialité</title>
</head>
<body>

<h2>Choisir votre spécialité</h2>

<form method="POST" action="/store-speciality">
    @csrf

```
<select name="speciality">
    <option value="math">Math</option>
    <option value="science">Science</option>
    <option value="info">Informatique</option>
    <option value="technique">Technique</option>
    <option value="eco">Economie et gestion</option>
</select>

<button type="submit">Continuer</button>
```

</form>

</body>
</html>
