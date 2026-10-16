
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Product | KrishiHat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 750px;">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">পণ্যের তথ্য সম্পাদনা করুন</h3>
        </div>

        <div class="card-body p-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">পণ্যের নাম *</label>
                    <input type="text" name="name" class="form-control"
                           value="{{ old('name', $product->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">ক্যাটাগরি</label>
                    <input type="text" name="category" class="form-control"
                           value="{{ old('category', $product->category) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">বিবরণ</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">দাম (৳) *</label>
                        <input type="number" name="price" class="form-control"
                               min="0" step="0.01"
                               value="{{ old('price', $product->price) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">একক *</label>
                        <input type="text" name="unit" class="form-control"
                               value="{{ old('unit', $product->unit) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">স্টকের পরিমাণ *</label>
                    <input type="number" name="stock" class="form-control"
                           min="0" step="1"
                           value="{{ old('stock', $product->stock) }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">ছবির URL (ঐচ্ছিক)</label>
                    <input type="url" name="image" class="form-control"
                           value="{{ old('image', $product->image) }}">
                </div>

                <button type="submit" class="btn btn-primary">পরিবর্তন সংরক্ষণ করুন</button>
                <a href="{{ route('farmer.dashboard') }}" class="btn btn-secondary">বাতিল</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>