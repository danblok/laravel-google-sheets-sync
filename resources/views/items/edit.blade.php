@extends('layouts.app')

@section('title', 'Изменение записи #'.$item->id)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Изменение записи #{{ $item->id }}
                </h4>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('items.update', $item) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag me-1"></i>Название <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $item->name) }}" required
                               placeholder="Введите название">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Описание
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="4"
                                  placeholder="Введите описание (опционально)">{{ old('description', $item->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">
                            <i class="fas fa-toggle-on me-1"></i>Статус <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option disabled selected value="">Выберите статус...</option>
                            <option value="Allowed" {{ old('status', $item->status) === 'Allowed' ? 'selected' : '' }}>
                                Allowed
                            </option>
                            <option value="Prohibited" {{ old('status', $item->status) === 'Prohibited' ? 'selected' : '' }}>
                                Prohibited
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="{{ route('items.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Отменить
                            </a>
                            <a href="{{ route('items.show', $item) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-2"></i>Просмотр
                            </a>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Обновить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
