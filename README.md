# Laravel Blade Scaffold

[![Latest Version on Packagist](https://img.shields.io/packagist/v/amptech/laravel-blade-scaffold.svg?style=flat-square)](https://packagist.org/packages/amptech/laravel-blade-scaffold)
[![Total Downloads](https://img.shields.io/packagist/dt/amptech/laravel-blade-scaffold.svg?style=flat-square)](https://packagist.org/packages/amptech/laravel-blade-scaffold)
[![License](https://img.shields.io/packagist/l/amptech/laravel-blade-scaffold.svg?style=flat-square)](https://packagist.org/packages/amptech/laravel-blade-scaffold)

Generador automático de vistas CRUD Blade para Laravel con componentes reutilizables y detección inteligente de tipos de campo.

---

## ✨ Características

- 🚀 **Generación automática** de vistas CRUD completas (index, create, edit, show, form)
- 🧠 **Detección inteligente** de tipos de campo basada en nombres
- 🔗 **Soporte para Foreign Keys** con generación automática de selects
- 🎨 **Componentes Blade reutilizables** listos para usar
- 💅 **Diseño moderno** con Tailwind CSS
- ⚙️ **Altamente personalizable** - publica y modifica templates
- 🔍 **Validación interactiva** antes de sobrescribir archivos
- 📦 **Compatible** con Laravel 8, 9, 10, 11

---

## 📦 Instalación

```bash
composer require amptech/laravel-blade-scaffold
```

### Publicar Assets

```bash
# Publicar todo (templates y components)
php artisan vendor:publish --tag=blade-scaffold

# Solo templates
php artisan vendor:publish --tag=blade-scaffold-templates

# Solo components
php artisan vendor:publish --tag=blade-scaffold-components
```

> **Nota**: El comando te preguntará automáticamente si deseas publicar los assets la primera vez que lo ejecutes.

---

## 🚀 Uso

### Comando Básico

```bash
php artisan make:form User
```

El comando te preguntará en qué ruta deseas crear las vistas. Por defecto usa `forms`.

### Ejemplos de Uso

```bash
# Especificar ruta personalizada
php artisan make:form Product --path=admin/products

# Generar solo algunas vistas específicas
php artisan make:form User --only=index,form

# Excluir vistas específicas
php artisan make:form Post --exclude=show,edit

# Forzar sobrescritura sin confirmación
php artisan make:form Category --force
```

---

## 📋 Vistas Generadas

El comando genera automáticamente:

| Vista | Descripción |
|-------|-------------|
| `index.blade.php` | Listado con tabla de registros y paginación |
| `create.blade.php` | Formulario para crear nuevo registro |
| `edit.blade.php` | Formulario para editar registro existente |
| `show.blade.php` | Vista de detalle del registro |
| `forms/form.blade.php` | Formulario reutilizable compartido |

### Estructura Generada

```
resources/views/
└── {ruta-especificada}/
    ├── index.blade.php
    ├── create.blade.php
    ├── edit.blade.php
    ├── show.blade.php
    └── forms/
        └── form.blade.php
```

---

## 🎨 Características Avanzadas

### ✅ Detección Automática de Campos

El paquete detecta automáticamente los campos `fillable` de tu modelo:

```php
class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'is_active',
        'stock',
        'email'
    ];
}
```

### ✅ Inferencia Inteligente de Tipos

Detecta automáticamente el tipo de campo según el nombre:

| Patrón en el nombre | Tipo de campo generado |
|---------------------|------------------------|
| `email` | `<input type="email">` |
| `password`, `pass` | `<input type="password">` |
| `phone`, `tel` | `<input type="tel">` |
| `url`, `link`, `website` | `<input type="url">` |
| `date`, `born`, `birth` | `<input type="date">` |
| `time` | `<input type="time">` |
| `price`, `amount`, `cost`, `age` | `<input type="number">` |
| `description`, `content`, `body`, `text` | `<textarea>` |
| `status`, `type`, `category`, `role` | `<select>` |
| `active`, `enabled`, `published` | `<checkbox>` |
| `*_id` | `<select>` con foreign key |

### ✅ Soporte para Foreign Keys

Detecta automáticamente campos con `_id` y genera selects con relaciones:

```php
// Campo: category_id en el modelo Product
// Genera automáticamente:
```

```blade
<x-inputs.select name="category_id" label="Category">
    <option value="">{{ __('Select...') }}</option>
    @foreach($categories as $category)
        <option value="{{ $category->id }}">
            {{ $category->name }}
        </option>
    @endforeach
</x-inputs.select>
```

**Importante:** Debes pasar la variable `$categories` desde tu controlador:

```php
public function create()
{
    $categories = Category::all();
    return view('products.create', compact('categories'));
}
```

---

## 🎨 Componentes Blade Incluidos

El paquete incluye componentes reutilizables listos para usar:

### `<x-form-group>`
Contenedor para campos de formulario con espaciado consistente.

```blade
<x-form-group>
    <!-- Tu campo aquí -->
</x-form-group>
```

### `<x-label-group>`
Grupo de etiqueta con descripción (ideal para vistas de detalle).

```blade
<x-label-group 
    label="Product Name" 
    description="{{ $product->name }}"
/>
```

### `<x-inputs.input>`
Input de texto con validación y estilos.

```blade
<x-inputs.input 
    name="name" 
    type="text" 
    label="Product Name"
    value="{{ old('name', $product->name ?? '') }}"
    required
/>
```

### `<x-inputs.textarea>`
Área de texto con validación.

```blade
<x-inputs.textarea 
    name="description" 
    label="Description"
    rows="4"
    value="{{ old('description', $product->description ?? '') }}"
/>
```

### `<x-inputs.select>`
Select dropdown con opciones.

```blade
<x-inputs.select name="category_id" label="Category" required>
    <option value="">Select...</option>
    @foreach($categories as $category)
        <option value="{{ $category->id }}">{{ $category->name }}</option>
    @endforeach
</x-inputs.select>
```

### `<x-inputs.checkbox>`
Checkbox estilizado.

```blade
<x-inputs.checkbox 
    name="is_active" 
    label="Active"
    value="1"
    :checked="old('is_active', $product->is_active ?? false)"
/>
```

---

## 🎯 Ejemplos Completos

### Ejemplo 1: CRUD de Productos

**1. Crear el modelo:**
```bash
php artisan make:model Product -m
```

**2. Definir fillable:**
```php
// app/Models/Product.php
protected $fillable = [
    'name',
    'description',
    'price',
    'category_id',
    'is_active'
];
```

**3. Generar vistas:**
```bash
php artisan make:form Product --path=admin/products
```

**4. Crear controlador:**
```bash
php artisan make:controller Admin/ProductController --resource
```

**5. Definir rutas:**
```php
// routes/web.php
Route::resource('admin/products', Admin\ProductController::class);
```

### Ejemplo 2: Solo Listado y Formulario

```bash
php artisan make:form Product --path=products --only=index,form
```

Genera solo:
- `resources/views/products/index.blade.php`
- `resources/views/products/forms/form.blade.php`

### Ejemplo 3: Sin Vista de Detalle

```bash
php artisan make:form Post --path=blog/posts --exclude=show
```

Genera todas las vistas excepto `show.blade.php`.

---

## 🔧 Personalización

### Modificar Templates

Publica los templates y personalízalos según tus necesidades:

```bash
php artisan vendor:publish --tag=blade-scaffold-templates
```

Los templates se copiarán a:
```
resources/views/vendor/blade-scaffold/templates/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
├── show.blade.php
└── form.blade.php
```

### Modificar Components

Publica los componentes y personalízalos:

```bash
php artisan vendor:publish --tag=blade-scaffold-components
```

Los componentes se copiarán a:
```
resources/views/components/
├── form-group.blade.php
├── label-group.blade.php
└── inputs/
    ├── input.blade.php
    ├── textarea.blade.php
    ├── select.blade.php
    └── checkbox.blade.php
```

### Ejemplo de Personalización

Una vez publicados, puedes modificar cualquier template. Por ejemplo, para cambiar el diseño de `index.blade.php`:

```blade
<!-- resources/views/vendor/blade-scaffold/templates/index.blade.php -->
<x-app-layout>
    <!-- Tu diseño personalizado aquí -->
</x-app-layout>
```

---

## 💡 Tips y Mejores Prácticas

### 1. Define los campos fillable
Asegúrate de definir los campos `fillable` en tu modelo para mejor detección:

```php
protected $fillable = ['name', 'email', 'phone', 'is_active'];
```

### 2. Usa nombres descriptivos
Los nombres de campos descriptivos permiten mejor inferencia de tipos:

✅ **Bueno**: `birth_date`, `is_active`, `description`  
❌ **Malo**: `date1`, `flag`, `text`

### 3. Personaliza los templates
Publica los templates y modifícalos según tu diseño corporativo:

```bash
php artisan vendor:publish --tag=blade-scaffold-templates
```

### 4. Recuerda pasar variables para foreign keys
Para campos `*_id`, debes pasar las relaciones desde el controlador:

```php
public function create()
{
    $categories = Category::all();
    $brands = Brand::all();
    return view('products.create', compact('categories', 'brands'));
}
```

### 5. Usa la opción --force en desarrollo
Durante el desarrollo, usa `--force` para sobrescribir rápidamente:

```bash
php artisan make:form Product --force
```

---

## 🛠 Troubleshooting

### No se generan vistas

**Problema:** El modelo no existe.

**Solución:**
```bash
php artisan make:model Product -m
```

### Templates no encontrados

**Problema:** Los templates no están publicados.

**Solución:**
```bash
php artisan vendor:publish --tag=blade-scaffold-templates
```

### Components no funcionan

**Problema:** Los componentes no están publicados.

**Solución:**
```bash
php artisan vendor:publish --tag=blade-scaffold-components
```

### Error: "Class not found"

**Problema:** Autoload de Composer no está actualizado.

**Solución:**
```bash
composer dump-autoload
```

### Los estilos no se aplican

**Problema:** Tailwind CSS no está configurado.

**Solución:**
1. Asegúrate de tener Tailwind CSS instalado
2. Verifica que los componentes usen las clases de Tailwind
3. Ejecuta `npm run dev` para compilar los assets

---

## 📋 Opciones del Comando

| Opción | Descripción | Ejemplo |
|--------|-------------|---------|
| `--path` | Ruta personalizada para las vistas | `--path=admin/products` |
| `--only` | Generar solo vistas específicas | `--only=index,form` |
| `--exclude` | Excluir vistas específicas | `--exclude=show,edit` |
| `--force` | Forzar sobrescritura sin confirmación | `--force` |

---

## 📝 Requisitos

- **PHP**: ^7.4 \| ^8.0 \| ^8.1 \| ^8.2 \| ^8.3
- **Laravel**: ^8.0 \| ^9.0 \| ^10.0 \| ^11.0
- **Tailwind CSS**: Recomendado (pero puedes personalizar)

---

## 🤝 Contribuir

¡Las contribuciones son bienvenidas! Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Guías para Contribuir

- Mantén el código limpio y documentado
- Sigue las convenciones de Laravel
- Agrega tests para nuevas funcionalidades
- Actualiza el CHANGELOG.md

---

## 🔄 Changelog

Consulta el archivo [CHANGELOG.md](CHANGELOG.md) para ver todos los cambios.

---

## 📄 Licencia

Este paquete es software de código abierto bajo la [Licencia MIT](LICENSE).

---

## 👤 Autor

**Oscar Amperez**

- Email: [oamperezp@gmail.com](mailto:oamperezp@gmail.com)
- GitHub: [@oamperezp](https://github.com/oamperezp)

---

## 🔗 Links

- [Repositorio en GitHub](https://github.com/oamperezp/laravel-blade-scaffold)
- [Reportar Issues](https://github.com/oamperezp/laravel-blade-scaffold/issues)
- [Packagist](https://packagist.org/packages/amptech/laravel-blade-scaffold)
- [Changelog](CHANGELOG.md)

---

## 🙏 Agradecimientos

- Gracias a la comunidad de Laravel
- Inspirado por Laravel Generators y otros paquetes similares
- Diseño basado en Tailwind CSS y Laravel Jetstream

---

## ⭐ ¿Te gusta este paquete?

Si este paquete te ayuda, considera:

- ⭐ Darle una estrella en [GitHub](https://github.com/oamperezp/laravel-blade-scaffold)
- 🐛 Reportar bugs o sugerir mejoras
- 💬 Compartirlo con la comunidad
- ☕ [Invitarme un café](https://github.com/sponsors/oamperezp)

---

## 📊 Estado del Proyecto

![GitHub issues](https://img.shields.io/github/issues/oamperezp/laravel-blade-scaffold)
![GitHub stars](https://img.shields.io/github/stars/oamperezp/laravel-blade-scaffold)
![GitHub forks](https://img.shields.io/github/forks/oamperezp/laravel-blade-scaffold)

---

**¡Desarrollado con ❤️ para la comunidad Laravel!**