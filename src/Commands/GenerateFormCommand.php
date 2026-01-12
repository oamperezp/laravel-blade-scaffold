<?php

namespace AmpTech\LaravelBladeScaffold\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateFormCommand extends Command
{
    protected $signature = 'make:form {model} 
                            {--path= : Ruta donde se creará el formulario}
                            {--only= : Generar solo vistas específicas separadas por coma (ej: index,form)}
                            {--exclude= : Vistas a excluir separadas por coma (ej: show,edit)}
                            {--force : Forzar sobrescritura de archivos existentes}';

    protected $description = 'Genera vistas CRUD (index, create, edit, show) y formulario';

    private $templates = [];
    private $excludedViews = [];
    private $onlyViews = [];

    public function handle()
    {
        $modelName = $this->argument('model');
        $customPath = $this->option('path');
        $exclude = $this->option('exclude');
        $only = $this->option('only');

        if (!$customPath) {
            $customPath = $this->ask('¿En qué ruta deseas crear las vistas? (ej: admin/users, dashboard/products)', 'forms');
        }

        if ($only && $exclude) {
            $this->error("No puedes usar --only y --exclude al mismo tiempo.");
            return 1;
        }

        if ($only) {
            $this->onlyViews = array_map('trim', explode(',', strtolower($only)));
            $validViews = ['index', 'create', 'edit', 'show', 'form'];
            $invalidViews = array_diff($this->onlyViews, $validViews);

            if (!empty($invalidViews)) {
                $this->error("Vistas inválidas: " . implode(', ', $invalidViews));
                $this->line("Vistas válidas: " . implode(', ', $validViews));
                return 1;
            }

            $this->info("📝 Generando solo: " . implode(', ', $this->onlyViews));
        } elseif ($exclude) {
            $this->excludedViews = array_map('trim', explode(',', strtolower($exclude)));
            $this->info("🚫 Vistas excluidas: " . implode(', ', $this->excludedViews));
        }

        $modelClass = "App\\Models\\{$modelName}";
        if (!class_exists($modelClass)) {
            if (class_exists($modelName)) {
                $modelClass = $modelName;
            } else {
                $this->error("El modelo {$modelName} no existe en App\\Models\\");
                $this->line("💡 Crea el modelo primero con: php artisan make:model {$modelName} -m");
                return 1;
            }
        }

        try {
            $fields = $this->getModelFields($modelClass);

            if (empty($fields)) {
                $this->warn("No se pudieron obtener campos del modelo {$modelName}. Creando formulario básico.");
                $fields = ['name' => 'string', 'description' => 'text'];
            }

            $this->loadTemplates();
            $this->createDirectories($customPath);

            if (!$this->option('force') && !$this->confirmOverwrite($customPath)) {
                $this->warn("❌ Operación cancelada.");
                return 0;
            }

            $generatedFiles = [];

            if ($this->shouldGenerate('index')) {
                $this->generateIndexView($customPath, $modelName, $fields);
                $generatedFiles[] = 'index.blade.php';
            }

            if ($this->shouldGenerate('create')) {
                $this->generateCreateView($customPath, $modelName, $fields);
                $generatedFiles[] = 'create.blade.php';
            }

            if ($this->shouldGenerate('edit')) {
                $this->generateEditView($customPath, $modelName, $fields);
                $generatedFiles[] = 'edit.blade.php';
            }

            if ($this->shouldGenerate('show')) {
                $this->generateShowView($customPath, $modelName, $fields);
                $generatedFiles[] = 'show.blade.php';
            }

            if ($this->shouldGenerate('form')) {
                $this->generateFormView($customPath, $modelName, $fields);
                $generatedFiles[] = 'forms/form.blade.php';
            }

            if (empty($generatedFiles)) {
                $this->warn("No se generó ninguna vista.");
                return 0;
            }

            $this->newLine();
            $this->info("✅ Vistas CRUD creadas exitosamente en: resources/views/{$customPath}/");
            $this->info("📁 Archivos generados:");
            foreach ($generatedFiles as $file) {
                $this->line("   - {$file}");
            }
            $this->line("📋 Campos detectados: " . implode(', ', array_keys($fields)));

            $this->showReminders($fields);

        } catch (\Exception $e) {
            $this->error("Error al generar las vistas: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    private function confirmOverwrite(string $path): bool
    {
        $basePath = resource_path("views/{$path}");
        $existingFiles = [];

        $filesToCheck = [
            'index.blade.php',
            'create.blade.php',
            'edit.blade.php',
            'show.blade.php',
            'forms/form.blade.php'
        ];

        foreach ($filesToCheck as $file) {
            if (File::exists("{$basePath}/{$file}")) {
                $existingFiles[] = $file;
            }
        }

        if (empty($existingFiles)) {
            return true;
        }

        $this->warn("⚠️  Los siguientes archivos ya existen y serán sobrescritos:");
        foreach ($existingFiles as $file) {
            $this->line("   - {$file}");
        }

        return $this->confirm('¿Deseas continuar y sobrescribir estos archivos?', false);
    }

    private function showReminders(array $fields): void
    {
        $hasForeignKeys = false;
        $foreignKeys = [];

        foreach ($fields as $fieldName => $fieldType) {
            if ($fieldType === 'foreign_key') {
                $hasForeignKeys = true;
                $relationName = Str::beforeLast($fieldName, '_id');
                $relationModelName = Str::studly(Str::singular($relationName));
                $relationVariable = Str::plural(Str::camel($relationName));
                $foreignKeys[] = "\${$relationVariable} = {$relationModelName}::all();";
            }
        }

        if ($hasForeignKeys) {
            $this->newLine();
            $this->warn("⚠️  RECORDATORIO: Debes pasar las siguientes variables desde tu controlador:");
            foreach ($foreignKeys as $fk) {
                $this->line("   {$fk}");
            }
        }

        $this->newLine();
        $this->info("💡 Próximos pasos:");
        $this->line("   1. Crea las rutas en routes/web.php");
        $this->line("   2. Crea el controlador con: php artisan make:controller YourController --resource");
        $this->line("   3. Revisa y personaliza las vistas generadas según tus necesidades");
    }

    private function shouldGenerate(string $view): bool
    {
        if (!empty($this->onlyViews)) {
            return in_array(strtolower($view), $this->onlyViews);
        }

        if (!empty($this->excludedViews)) {
            return !in_array(strtolower($view), $this->excludedViews);
        }

        return true;
    }

    private function loadTemplates(): void
    {
        $templatesPath = $this->getTemplatesPath();
        $viewsToLoad = $this->getViewsToLoad();

        foreach ($viewsToLoad as $view) {
            $templateFile = "{$templatesPath}/{$view}.blade.php";
            $this->templates[$view] = File::get($templateFile);
        }
    }

    private function getViewsToLoad(): array
    {
        if (!empty($this->onlyViews)) {
            return $this->onlyViews;
        }

        $allViews = ['index', 'create', 'edit', 'show', 'form'];

        if (!empty($this->excludedViews)) {
            return array_diff($allViews, $this->excludedViews);
        }

        return $allViews;
    }

    private function getTemplatesPath(): string
    {
        $publishedPath = resource_path('views/vendor/blade-scaffold/templates');
        if (File::isDirectory($publishedPath)) {
            return $publishedPath;
        }

        return __DIR__ . '/../templates';
    }

    private function createDirectories(string $path): void
    {
        $basePath = resource_path("views/{$path}");
        $formsPath = "{$basePath}/forms";

        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        if ($this->shouldGenerate('form') && !File::exists($formsPath)) {
            File::makeDirectory($formsPath, 0755, true);
        }
    }

    private function generateIndexView(string $path, string $modelName, array $fields): void
    {
        $modelLower = Str::lower($modelName);
        $modelPlural = Str::plural($modelLower);
        $modelTitle = Str::title($modelName);
        $modelTitlePlural = Str::plural($modelTitle);
        $modelVariable = Str::camel($modelName);

        $tableHeaders = $this->generateTableHeaders($fields);
        $tableColumns = $this->generateTableColumns($fields, $modelVariable);

        $replacements = [
            '{MODEL_PLURAL}' => $modelPlural,
            '{MODEL_TITLE}' => $modelTitle,
            '{MODEL_TITLE_PLURAL}' => $modelTitlePlural,
            '{MODEL_VARIABLE}' => $modelVariable,
            '{TABLE_HEADERS}' => $tableHeaders,
            '{TABLE_COLUMNS}' => $tableColumns,
        ];

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->templates['index']
        );

        File::put(resource_path("views/{$path}/index.blade.php"), $content);
    }

    private function generateCreateView(string $path, string $modelName, array $fields): void
    {
        $modelLower = Str::lower($modelName);
        $modelPlural = Str::plural($modelLower);
        $modelTitle = Str::title($modelName);

        $replacements = [
            '{MODEL_PLURAL}' => $modelPlural,
            '{MODEL_TITLE}' => $modelTitle,
            '{PATH}' => $path,
        ];

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->templates['create']
        );

        File::put(resource_path("views/{$path}/create.blade.php"), $content);
    }

    private function generateEditView(string $path, string $modelName, array $fields): void
    {
        $modelLower = Str::lower($modelName);
        $modelPlural = Str::plural($modelLower);
        $modelTitle = Str::title($modelName);
        $modelVariable = Str::camel($modelName);

        $replacements = [
            '{MODEL_PLURAL}' => $modelPlural,
            '{MODEL_TITLE}' => $modelTitle,
            '{MODEL_VARIABLE}' => $modelVariable,
            '{PATH}' => $path,
        ];

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->templates['edit']
        );

        File::put(resource_path("views/{$path}/edit.blade.php"), $content);
    }

    private function generateShowView(string $path, string $modelName, array $fields): void
    {
        $modelLower = Str::lower($modelName);
        $modelPlural = Str::plural($modelLower);
        $modelTitle = Str::title($modelName);
        $modelVariable = Str::camel($modelName);

        $showFields = $this->generateShowFields($fields, $modelVariable);

        $replacements = [
            '{MODEL_PLURAL}' => $modelPlural,
            '{MODEL_TITLE}' => $modelTitle,
            '{MODEL_VARIABLE}' => $modelVariable,
            '{SHOW_FIELDS}' => $showFields,
        ];

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->templates['show']
        );

        File::put(resource_path("views/{$path}/show.blade.php"), $content);
    }

    private function generateFormView(string $path, string $modelName, array $fields): void
    {
        $modelVariable = Str::camel($modelName);
        $formFields = '';

        foreach ($fields as $fieldName => $fieldType) {
            $label = Str::title(str_replace('_', ' ', $fieldName));
            $formFields .= $this->generateFieldHtml($fieldName, $fieldType, $label, $modelVariable);
        }

        $content = str_replace('{FORM_FIELDS}', $formFields, $this->templates['form']);

        File::put(resource_path("views/{$path}/forms/form.blade.php"), $content);
    }

    private function generateTableHeaders(array $fields): string
    {
        $headers = '';
        $visibleFields = array_slice($fields, 0, 5, true);

        foreach ($visibleFields as $fieldName => $fieldType) {
            $label = Str::title(str_replace('_', ' ', $fieldName));
            $headers .= "                                    <th class=\"min-w-[150px]\">{$label}</th>\n";
        }

        return rtrim($headers);
    }

    private function generateTableColumns(array $fields, string $modelVariable): string
    {
        $columns = '';
        $visibleFields = array_slice($fields, 0, 5, true);

        foreach ($visibleFields as $fieldName => $fieldType) {
            $columns .= "                                        <td>{{ Str::limit(\${$modelVariable}->{$fieldName}, 50) }}</td>\n";
        }

        return rtrim($columns);
    }

    private function generateShowFields(array $fields, string $modelVariable): string
    {
        $showFields = '';

        foreach ($fields as $fieldName => $fieldType) {
            $label = Str::title(str_replace('_', ' ', $fieldName));
            $showFields .= <<<EOT
                <x-label-group label="{$label}" description="{{ \${$modelVariable}->{$fieldName} ?? __('Not specified') }}"/>

EOT;
        }

        return $showFields;
    }

    private function getModelFields($modelClass): array
    {
        $fields = [];

        try {
            $model = new $modelClass();
            $fillable = $model->getFillable();

            if (empty($fillable)) {
                $this->warn("El modelo no tiene campos fillable definidos. Usando campos por defecto.");
                return [
                    'name' => 'string',
                    'email' => 'email',
                    'description' => 'textarea'
                ];
            }

            foreach ($fillable as $field) {
                $fields[$field] = $this->inferFieldType($field);
            }

        } catch (\Exception $e) {
            $this->warn("No se pudo instanciar el modelo: " . $e->getMessage());
        }

        return $fields;
    }

    private function inferFieldType(string $fieldName): string
    {
        $fieldName = strtolower($fieldName);

        if (Str::endsWith($fieldName, '_id')) {
            return 'foreign_key';
        }

        if (Str::contains($fieldName, ['email']))
            return 'email';
        if (Str::contains($fieldName, ['password', 'pass']))
            return 'password';
        if (Str::contains($fieldName, ['phone', 'tel']))
            return 'tel';
        if (Str::contains($fieldName, ['url', 'link', 'website']))
            return 'url';
        if (Str::contains($fieldName, ['date', 'born', 'birth']))
            return 'date';
        if (Str::contains($fieldName, ['time']))
            return 'time';
        if (Str::contains($fieldName, ['number', 'amount', 'price', 'cost', 'age', 'quantity']))
            return 'number';
        if (Str::contains($fieldName, ['description', 'content', 'body', 'text', 'message', 'notes', 'comment']))
            return 'textarea';
        if (Str::contains($fieldName, ['status', 'type', 'category', 'role']))
            return 'select';
        if (Str::contains($fieldName, ['active', 'enabled', 'published', 'visible', 'featured']))
            return 'checkbox';

        return 'text';
    }

    private function generateFieldHtml(string $fieldName, string $fieldType, string $label, string $modelVariable): string
    {
        if ($fieldType === 'foreign_key') {
            return $this->generateForeignKeySelect($fieldName, $label, $modelVariable);
        }

        if ($fieldType === 'password') {
            return <<<EOT
        <x-form-group>
            <x-inputs.input 
                name="{$fieldName}" 
                type="password" 
                label="{{ __('{$label}') }}"
                autocomplete="{$fieldName}" 
                required
            />
        </x-form-group>

EOT;
        }

        $oldValue = "{{ old('{$fieldName}', \${$modelVariable}->{$fieldName} ?? '') }}";

        switch ($fieldType) {
            case 'textarea':
                return <<<EOT
        <x-form-group>
            <x-inputs.textarea 
                name="{$fieldName}" 
                label="{{ __('{$label}') }}"
                rows="4"
                value="{$oldValue}"
                required
            />
        </x-form-group>

EOT;

            case 'select':
                return <<<EOT
        <x-form-group>
            <x-inputs.select 
                name="{$fieldName}" 
                label="{{ __('{$label}') }}"
                value="{$oldValue}"
                required
            >
                <option value="">{{ __('Select...') }}</option>
            </x-inputs.select>
        </x-form-group>

EOT;

            case 'checkbox':
                return <<<EOT
        <x-form-group>
            <x-inputs.checkbox 
                name="{$fieldName}" 
                label="{{ __('{$label}') }}"
                value="1"
                :checked="old('{$fieldName}', \${$modelVariable}->{$fieldName} ?? false)"
            />
        </x-form-group>

EOT;

            default:
                return <<<EOT
        <x-form-group>
            <x-inputs.input 
                name="{$fieldName}" 
                type="{$fieldType}" 
                label="{{ __('{$label}') }}"
                value="{$oldValue}"
                autocomplete="{$fieldName}" 
                required
            />
        </x-form-group>

EOT;
        }
    }

    private function generateForeignKeySelect(string $fieldName, string $label, string $modelVariable): string
    {
        $relationName = Str::beforeLast($fieldName, '_id');
        $relationModelName = Str::studly(Str::singular($relationName));
        $relationVariable = Str::plural(Str::camel($relationName));

        $oldValue = "{{ old('{$fieldName}', \${$modelVariable}->{$fieldName} ?? '') }}";

        return <<<EOT
        <x-form-group>
            <x-inputs.select 
                name="{$fieldName}" 
                label="{{ __('{$label}') }}"
                value="{$oldValue}"
                required
            >
                <option value="">{{ __('Select...') }}</option>
                @foreach(\${$relationVariable} as \${$relationName})
                    <option value="{{ \${$relationName}->id }}" {{ old('{$fieldName}', \${$modelVariable}->{$fieldName} ?? '') == \${$relationName}->id ? 'selected' : '' }}>
                        {{ \${$relationName}->name }}
                    </option>
                @endforeach
            </x-inputs.select>
        </x-form-group>

EOT;
    }
}