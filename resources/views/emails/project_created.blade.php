<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>New Project Created</title>
  </head>
  <body>
    <h1>New Project: {{ $project->name }}</h1>
    <p>{{ $creator->name }} created a new project.</p>
    @if(!empty($project->description))
      <p>{{ $project->description }}</p>
    @endif
    <p>
      View projects in the app to see more details.
    </p>
  </body>
</html>
