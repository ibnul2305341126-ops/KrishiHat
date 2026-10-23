@csrf
<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input id="name" name="name" class="form-control" maxlength="255" required value="{{ old('name', $category?->name) }}">
    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="slug" class="form-label">Slug</label>
    <input id="slug" name="slug" class="form-control" maxlength="140" required value="{{ old('slug', $category?->slug) }}" aria-describedby="slug-help">
    <div id="slug-help" class="form-text">Use a unique URL-friendly value, such as vegetables.</div>
    @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea id="description" name="description" class="form-control" rows="3" maxlength="2000">{{ old('description', $category?->description) }}</textarea>
    @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
<div class="mb-4">
    <label for="icon" class="form-label">Icon or emoji <span class="text-secondary">(optional)</span></label>
    <input id="icon" name="icon" class="form-control" maxlength="64" value="{{ old('icon', $category?->icon) }}" placeholder="For example: 🥬">
    @error('icon')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
<button class="btn btn-success" type="submit">{{ $submitLabel }}</button>
<a class="btn btn-outline-secondary" href="{{ route('admin.categories.index') }}">Cancel</a>
