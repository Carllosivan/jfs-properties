<!DOCTYPE html>
<html lang="pt" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Autenticação - JFS Properties') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full font-sans antialiased flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-slate-100">
    <div class="w-full max-w-md">
        <?= $content ?? '' ?>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
