<!DOCTYPE html>

<html>
<head>
    <title>Notes</title>
</head>
<body>

<h2>Entrer vos notes</h2>

<form method="POST" action="/calculate-average">
    @csrf

```
<input type="number" step="0.01" name="math" placeholder="Math"><br>
<input type="number" step="0.01" name="physique" placeholder="Physique"><br>
<input type="number" step="0.01" name="science" placeholder="Science"><br>
<input type="number" step="0.01" name="arabe" placeholder="Arabe"><br>
<input type="number" step="0.01" name="francais" placeholder="Français"><br>
<input type="number" step="0.01" name="anglais" placeholder="Anglais"><br>
<input type="number" step="0.01" name="philosophie" placeholder="Philosophie"><br>

<button type="submit">Calculer</button>
```

</form>

</body>
</html>
