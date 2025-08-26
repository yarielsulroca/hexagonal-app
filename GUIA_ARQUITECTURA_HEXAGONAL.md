# 🏗️ Guía: API CRUD con Arquitectura Hexagonal en Laravel

## 📋 Resumen del Proyecto
Crear una API completa para gestionar:
- **Usuarios** (registro, login, CRUD)
- **Productos** (CRUD con relación 1:N con usuarios)
- **Categorías** (CRUD con relación N:N con productos)

## 🏛️ Estructura de Arquitectura Hexagonal COMPLETA

```
app/
├── Domain/                    # Capa de Dominio (Entidades, Reglas de Negocio)
│   ├── Entities/             # Entidades de dominio
│   ├── ValueObjects/         # Objetos de valor
│   ├── Services/             # Servicios de dominio
│   └── Events/               # Eventos de dominio
├── Application/               # Capa de Aplicación (Casos de Uso)
│   ├── UseCases/             # Casos de uso de la aplicación
│   ├── DTOs/                 # Objetos de transferencia de datos
│   ├── Commands/             # Comandos (CQRS)
│   └── Queries/              # Consultas (CQRS)
├── Infrastructure/            # Capa de Infraestructura (Implementaciones)
│   ├── Persistence/          # Persistencia de datos
│   ├── Http/                 # Controladores HTTP
│   ├── External/             # Servicios externos
│   └── Messaging/            # Mensajería
└── Shared/                   # Código Compartido
    ├── Exceptions/           # Excepciones personalizadas
    ├── Events/               # Eventos compartidos
    └── Utils/                # Utilidades
```

## 🔌 **PUERTOS Y ADAPTADORES (Arquitectura Hexagonal)**

### **📥 Puertos de Entrada (Input Ports):**
- **UserUseCase** → Define cómo se registran/autentican usuarios
- **ProductUseCase** → Define cómo se gestionan productos
- **CategoryUseCase** → Define cómo se gestionan categorías

### **📤 Puertos de Salida (Output Ports):**
- **UserRepository** → Define cómo se persisten usuarios
- **ProductRepository** → Define cómo se persisten productos
- **CategoryRepository** → Define cómo se persisten categorías

### **🔌 Adaptadores de Entrada (Input Adapters):**
- **AuthController** → Adapta peticiones HTTP a casos de uso
- **ProductController** → Adapta peticiones HTTP a casos de uso
- **CategoryController** → Adapta peticiones HTTP a casos de uso

### **🔌 Adaptadores de Salida (Output Adapters):**
- **EloquentUserRepository** → Implementa UserRepository con Eloquent
- **EloquentProductRepository** → Implementa ProductRepository con Eloquent
- **EloquentCategoryRepository** → Implementa CategoryRepository con Eloquent

---

## 🚀 PASO 1: Configuración Inicial

### 1.1 Instalar dependencias adicionales
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 1.2 Configurar Sanctum en config/auth.php
```php
'guards' => [
    'web' => ['driver' => 'session', 'provider' => 'users'],
    'api' => ['driver' => 'sanctum', 'provider' => 'users'],
],
```

---

## 🏗️ PASO 2: Crear Estructura de Carpetas COMPLETA

### 2.1 Crear directorios de arquitectura hexagonal
```bash
# Dominio
mkdir -p app/Domain/Entities
mkdir -p app/Domain/Repositories
mkdir -p app/Domain/ValueObjects
mkdir -p app/Domain/Services
mkdir -p app/Domain/Events

# Aplicación
mkdir -p app/Application/UseCases
mkdir -p app/Application/DTOs
mkdir -p app/Application/Commands
mkdir -p app/Application/Queries
mkdir -p app/Application/Ports

# Infraestructura
mkdir -p app/Infrastructure/Persistence/Eloquent
mkdir -p app/Infrastructure/Persistence/Repositories
mkdir -p app/Infrastructure/Http/Controllers
mkdir -p app/Infrastructure/Http/Requests
mkdir -p app/Infrastructure/Http/Resources
mkdir -p app/Infrastructure/Http/Middleware
mkdir -p app/Infrastructure/External
mkdir -p app/Infrastructure/Messaging

# Compartido
mkdir -p app/Shared/Exceptions
mkdir -p app/Shared/Events
mkdir -p app/Shared/Utils
```

---

## 🎯 PASO 3: Crear Entidades de Dominio

### 3.1 Entidad User (app/Domain/Entities/User.php)
```php
<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use App\Domain\Events\UserRegistered;

class User
{
    private array $events = [];

    public function __construct(
        private ?int $id,
        private string $name,
        private Email $email,
        private Password $password,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {}

    // Getters y métodos de dominio
    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function email(): Email { return $this->email; }
    public function createdAt(): \DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    // Método de dominio para registrar usuario
    public function register(): void
    {
        $this->events[] = new UserRegistered($this->id, $this->email->value());
    }

    // Obtener eventos pendientes
    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
```

### 3.2 Entidad Product (app/Domain/Entities/Product.php)
```php
<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\Stock;
use App\Domain\Events\ProductCreated;
use App\Domain\Events\ProductUpdated;

class Product
{
    private array $events = [];

    public function __construct(
        private ?int $id,
        private string $name,
        private string $description,
        private Price $price,
        private Stock $stock,
        private string $image,
        private int $userId,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {}

    // Getters y métodos de dominio
    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function price(): Price { return $this->price; }
    public function stock(): Stock { return $this->stock; }
    public function userId(): int { return $this->userId; }

    // Método de dominio para actualizar precio
    public function updatePrice(Price $newPrice): void
    {
        $this->price = $newPrice;
        $this->events[] = new ProductUpdated($this->id, 'price', $newPrice->value());
    }

    // Método de dominio para actualizar stock
    public function updateStock(Stock $newStock): void
    {
        $this->stock = $newStock;
        $this->events[] = new ProductUpdated($this->id, 'stock', $newStock->value());
    }

    // Obtener eventos pendientes
    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
```

### 3.3 Entidad Category (app/Domain/Entities/Category.php)
```php
<?php

namespace App\Domain\Entities;

class Category
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $description,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt
    ) {}

    // Getters
    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function description(): string { return $this->description; }
}
```

---

## 🔧 PASO 4: Crear Value Objects

### 4.1 Email (app/Domain/ValueObjects/Email.php)
```php
<?php

namespace App\Domain\ValueObjects;

class Email
{
    public function __construct(private string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido');
        }
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
    
    // Método para comparar emails
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
```

### 4.2 Price (app/Domain/ValueObjects/Price.php)
```php
<?php

namespace App\Domain\ValueObjects;

class Price
{
    public function __construct(private float $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo');
        }
    }

    public function value(): float { return $this->value; }
    public function __toString(): string { return number_format($this->value, 2); }
    
    // Método para sumar precios
    public function add(Price $other): Price
    {
        return new Price($this->value + $other->value);
    }
    
    // Método para multiplicar por un factor
    public function multiply(float $factor): Price
    {
        return new Price($this->value * $factor);
    }
}
```

### 4.3 Stock (app/Domain/ValueObjects/Stock.php)
```php
<?php

namespace App\Domain\ValueObjects;

class Stock
{
    public function __construct(private int $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('El stock no puede ser negativo');
        }
    }

    public function value(): int { return $this->value; }
    public function __toString(): string { return (string) $this->value; }
}
```

---

## 📝 PASO 5: Crear PUERTOS (Interfaces)

### 5.1 UserRepository (app/Domain/Repositories/UserRepository.php) - PUERTO DE SALIDA
```php
<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\User;

interface UserRepository
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): User;
    public function delete(int $id): bool;
    public function findAll(): array;
}
```

### 5.2 UserService (app/Domain/Services/UserService.php) - PUERTO DE ENTRADA
```php
<?php

namespace App\Domain\Services;

use App\Domain\Entities\User;
use App\Domain\ValueObjects\Email;

interface UserService
{
    public function registerUser(string $name, Email $email, string $password): User;
    public function authenticateUser(Email $email, string $password): ?User;
    public function updateUser(int $id, array $data): User;
}
```

### 5.3 ProductRepository (app/Domain/Repositories/ProductRepository.php) - PUERTO DE SALIDA
```php
<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Product;

interface ProductRepository
{
    public function findById(int $id): ?Product;
    public function findByUserId(int $userId): array;
    public function save(Product $product): Product;
    public function delete(int $id): bool;
    public function findAll(): array;
}
```

### 5.4 ProductService (app/Domain/Services/ProductService.php) - PUERTO DE ENTRADA
```php
<?php

namespace App\Domain\Services;

use App\Domain\Entities\Product;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\Stock;

interface ProductService
{
    public function createProduct(string $name, string $description, Price $price, Stock $stock, string $image, int $userId): Product;
    public function updateProduct(int $id, array $data): Product;
    public function deleteProduct(int $id): bool;
    public function findAllProducts(): array;
    public function findProductsByUserId(int $userId): array;
}
```

### 5.5 CategoryRepository (app/Domain/Repositories/CategoryRepository.php) - PUERTO DE SALIDA
```php
<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Category;

interface CategoryRepository
{
    public function findById(int $id): ?Category;
    public function save(Category $category): Category;
    public function delete(int $id): bool;
    public function findAll(): array;
}
```

### 5.6 CategoryService (app/Domain/Services/CategoryService.php) - PUERTO DE ENTRADA
```php
<?php

namespace App\Domain\Services;

use App\Domain\Entities\Category;

interface CategoryService
{
    public function createCategory(string $name, string $description): Category;
    public function updateCategory(int $id, array $data): Category;
    public function deleteCategory(int $id): bool;
    public function findAllCategories(): array;
}
```

---

## 🎯 PASO 6: Crear Casos de Uso (PUERTOS DE ENTRADA)

### 6.1 UserUseCase (app/Application/UseCases/UserUseCase.php)
```php
<?php

namespace App\Application\UseCases;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepository;
use App\Domain\Services\UserService;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use App\Application\DTOs\UserDTO;
use App\Application\Ports\UserInputPort;

class UserUseCase implements UserInputPort
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService
    ) {}

    public function register(string $name, string $email, string $password): UserDTO
    {
        // Validar que el email no exista
        if ($this->userRepository->findByEmail($email)) {
            throw new \Exception('El email ya está registrado');
        }

        $user = $this->userService->registerUser(
            $name,
            new Email($email),
            $password
        );

        // Disparar eventos de dominio
        $user->register();

        return UserDTO::fromEntity($user);
    }

    public function authenticate(string $email, string $password): ?UserDTO
    {
        $user = $this->userService->authenticateUser(
            new Email($email),
            $password
        );
        
        return $user ? UserDTO::fromEntity($user) : null;
    }
}
```

### 6.2 ProductUseCase (app/Application/UseCases/ProductUseCase.php)
```php
<?php

namespace App\Application\UseCases;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepository;
use App\Domain\Services\ProductService;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\Stock;
use App\Application\DTOs\ProductDTO;
use App\Application\Ports\ProductInputPort;

class ProductUseCase implements ProductInputPort
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductService $productService
    ) {}

    public function create(string $name, string $description, float $price, int $stock, string $image, int $userId): ProductDTO
    {
        $product = $this->productService->createProduct(
            $name,
            $description,
            new Price($price),
            new Stock($stock),
            $image,
            $userId
        );

        return ProductDTO::fromEntity($product);
    }

    public function update(int $id, array $data): ProductDTO
    {
        $product = $this->productRepository->findById($id);
        if (!$product) {
            throw new \Exception('Producto no encontrado');
        }

        // Lógica de actualización
        $product = $this->productService->updateProduct($id, $data);

        return ProductDTO::fromEntity($product);
    }

    public function delete(int $id): bool
    {
        return $this->productService->deleteProduct($id);
    }

    public function findAll(): array
    {
        return $this->productRepository->findAll();
    }

    public function findByUserId(int $userId): array
    {
        return $this->productRepository->findByUserId($userId);
    }
}
```

### 6.3 CategoryUseCase (app/Application/UseCases/CategoryUseCase.php)
```php
<?php

namespace App\Application\UseCases;

use App\Domain\Entities\Category;
use App\Domain\Repositories\CategoryRepository;
use App\Domain\Services\CategoryService;
use App\Application\DTOs\CategoryDTO;
use App\Application\Ports\CategoryInputPort;

class CategoryUseCase implements CategoryInputPort
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private CategoryService $categoryService
    ) {}

    public function create(string $name, string $description): CategoryDTO
    {
        $category = $this->categoryService->createCategory(
            $name,
            $description
        );

        return CategoryDTO::fromEntity($category);
    }

    public function update(int $id, array $data): CategoryDTO
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new \Exception('Categoría no encontrada');
        }

        // Lógica de actualización
        $category = $this->categoryService->updateCategory($id, $data);

        return CategoryDTO::fromEntity($category);
    }

    public function delete(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }

    public function findAll(): array
    {
        return $this->categoryRepository->findAll();
    }
}
```

---

## 🔌 PASO 7: Crear ADAPTADORES DE ENTRADA

### 7.1 AuthController (app/Infrastructure/Http/Controllers/AuthController.php)
```php
<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Ports\UserInputPort;
use App\Infrastructure\Http\Requests\LoginRequest;
use App\Infrastructure\Http\Requests\RegisterRequest;
use App\Infrastructure\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private UserInputPort $userUseCase) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userDTO = $this->userUseCase->register(
                $request->name,
                $request->email,
                Hash::make($request->password)
            );

            // Aquí deberías crear el token usando el modelo Eloquent
            // $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => new UserResource($userDTO),
                'message' => 'Usuario registrado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $userDTO = $this->userUseCase->authenticate($request->email, $request->password);
        
        if (!$userDTO) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        return response()->json([
            'user' => new UserResource($userDTO),
            'message' => 'Login exitoso'
        ]);
    }
}
```

### 7.2 ProductController (app/Infrastructure/Http/Controllers/ProductController.php)
```php
<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Ports\ProductInputPort;
use App\Infrastructure\Http\Requests\ProductRequest;
use App\Infrastructure\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductInputPort $productUseCase) {}

    public function index(): JsonResponse
    {
        return response()->json(
            ProductResource::collection($this->productUseCase->findAll())
        );
    }

    public function store(ProductRequest $request): JsonResponse
    {
        try {
            $productDTO = $this->productUseCase->create(
                $request->name,
                $request->description,
                $request->price,
                $request->stock,
                $request->image,
                $request->user()->id
            );

            return response()->json([
                'product' => new ProductResource($productDTO),
                'message' => 'Producto creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productUseCase->findById($id);
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        return response()->json(new ProductResource($product));
    }

    public function update(ProductRequest $request, int $id): JsonResponse
    {
        try {
            $productDTO = $this->productUseCase->update($id, $request->all());
            return response()->json([
                'product' => new ProductResource($productDTO),
                'message' => 'Producto actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        if ($this->productUseCase->delete($id)) {
            return response()->json(['message' => 'Producto eliminado exitosamente']);
        }
        return response()->json(['error' => 'Producto no encontrado'], 404);
    }

    public function userProducts(Request $request): JsonResponse
    {
        return response()->json(
            ProductResource::collection($this->productUseCase->findProductsByUserId($request->user()->id))
        );
    }
}
```

### 7.3 CategoryController (app/Infrastructure/Http/Controllers/CategoryController.php)
```php
<?php

namespace App\Infrastructure\Http\Controllers;

use App\Application\Ports\CategoryInputPort;
use App\Infrastructure\Http\Requests\CategoryRequest;
use App\Infrastructure\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private CategoryInputPort $categoryUseCase) {}

    public function index(): JsonResponse
    {
        return response()->json(
            CategoryResource::collection($this->categoryUseCase->findAll())
        );
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        try {
            $categoryDTO = $this->categoryUseCase->create(
                $request->name,
                $request->description
            );

            return response()->json([
                'category' => new CategoryResource($categoryDTO),
                'message' => 'Categoría creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->categoryUseCase->findById($id);
        if (!$category) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }
        return response()->json(new CategoryResource($category));
    }

    public function update(CategoryRequest $request, int $id): JsonResponse
    {
        try {
            $categoryDTO = $this->categoryUseCase->update($id, $request->all());
            return response()->json([
                'category' => new CategoryResource($categoryDTO),
                'message' => 'Categoría actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        if ($this->categoryUseCase->delete($id)) {
            return response()->json(['message' => 'Categoría eliminada exitosamente']);
        }
        return response()->json(['error' => 'Categoría no encontrada'], 404);
    }
}
```

---

## 🔌 PASO 8: Crear ADAPTADORES DE SALIDA

### 8.1 EloquentUserRepository (app/Infrastructure/Persistence/Repositories/EloquentUserRepository.php)
```php
<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentUser;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Password;
use App\Shared\Exceptions\DomainException;

class EloquentUserRepository implements UserRepository
{
    public function findById(int $id): ?User
    {
        $eloquentUser = EloquentUser::find($id);
        return $eloquentUser ? $this->toDomain($eloquentUser) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $eloquentUser = EloquentUser::where('email', $email)->first();
        return $eloquentUser ? $this->toDomain($eloquentUser) : null;
    }

    public function save(User $user): User
    {
        try {
            if ($user->id()) {
                $eloquentUser = EloquentUser::find($user->id());
                $eloquentUser->update([
                    'name' => $user->name(),
                    'email' => $user->email()->value(),
                    'password' => $user->password()->value(),
                ]);
            } else {
                $eloquentUser = new EloquentUser();
                $eloquentUser->name = $user->name();
                $eloquentUser->email = $user->email()->value();
                $eloquentUser->password = $user->password()->value();
                $eloquentUser->save();
            }

            return $this->toDomain($eloquentUser);
        } catch (\Exception $e) {
            throw new DomainException('Error al guardar usuario: ' . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        return EloquentUser::destroy($id) > 0;
    }

    public function findAll(): array
    {
        return EloquentUser::all()->map(fn($user) => $this->toDomain($user))->toArray();
    }

    private function toDomain(EloquentUser $eloquentUser): User
    {
        return new User(
            $eloquentUser->id,
            $eloquentUser->name,
            new Email($eloquentUser->email),
            new Password($eloquentUser->password),
            $eloquentUser->created_at,
            $eloquentUser->updated_at
        );
    }
}
```

### 8.2 EloquentProductRepository (app/Infrastructure/Persistence/Repositories/EloquentProductRepository.php)
```php
<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentProduct;
use App\Domain\ValueObjects\Price;
use App\Domain\ValueObjects\Stock;
use App\Shared\Exceptions\DomainException;

class EloquentProductRepository implements ProductRepository
{
    public function findById(int $id): ?Product
    {
        $eloquentProduct = EloquentProduct::find($id);
        return $eloquentProduct ? $this->toDomain($eloquentProduct) : null;
    }

    public function findByUserId(int $userId): array
    {
        return EloquentProduct::where('user_id', $userId)->get()->map(fn($product) => $this->toDomain($product))->toArray();
    }

    public function save(Product $product): Product
    {
        try {
            if ($product->id()) {
                $eloquentProduct = EloquentProduct::find($product->id());
                $eloquentProduct->update([
                    'name' => $product->name(),
                    'description' => $product->description(),
                    'price' => $product->price()->value(),
                    'stock' => $product->stock()->value(),
                    'image' => $product->image(),
                ]);
            } else {
                $eloquentProduct = new EloquentProduct();
                $eloquentProduct->name = $product->name();
                $eloquentProduct->description = $product->description();
                $eloquentProduct->price = $product->price()->value();
                $eloquentProduct->stock = $product->stock()->value();
                $eloquentProduct->image = $product->image();
                $eloquentProduct->user_id = $product->userId();
                $eloquentProduct->save();
            }

            return $this->toDomain($eloquentProduct);
        } catch (\Exception $e) {
            throw new DomainException('Error al guardar producto: ' . $e->getMessage());
        }
    }

    public function delete(int $id): bool
    {
        return EloquentProduct::destroy($id) > 0;
    }

    public function findAll(): array
    {
        return EloquentProduct::all()->map(fn($product) => $this->toDomain($product))->toArray();
    }

    private function toDomain(EloquentProduct $eloquentProduct): Product
    {
        return new Product(
            $eloquentProduct->id,
            $eloquentProduct->name,
            $eloquentProduct->description,
            new Price($eloquentProduct->price),
            new Stock($eloquentProduct->stock),
            $eloquentProduct->image,
            $eloquentProduct->user_id,
            $eloquentProduct->created_at,
            $eloquentProduct->updated_at
        );
    }
}
```

### 8.3 EloquentCategoryRepository (app/Infrastructure/Persistence/Repositories/EloquentCategoryRepository.php)
```php
<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Entities\Category;
use App\Domain\Repositories\CategoryRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCategory;

class EloquentCategoryRepository implements CategoryRepository
{
    public function findById(int $id): ?Category
    {
        $eloquentCategory = EloquentCategory::find($id);
        return $eloquentCategory ? $this->toDomain($eloquentCategory) : null;
    }

    public function save(Category $category): Category
    {
        if ($category->id()) {
            $eloquentCategory = EloquentCategory::find($category->id());
            $eloquentCategory->update([
                'name' => $category->name(),
                'description' => $category->description(),
            ]);
        } else {
            $eloquentCategory = new EloquentCategory();
            $eloquentCategory->name = $category->name();
            $eloquentCategory->description = $category->description();
            $eloquentCategory->save();
        }

        return $this->toDomain($eloquentCategory);
    }

    public function delete(int $id): bool
    {
        return EloquentCategory::destroy($id) > 0;
    }

    public function findAll(): array
    {
        return EloquentCategory::all()->map(fn($category) => $this->toDomain($category))->toArray();
    }

    private function toDomain(EloquentCategory $eloquentCategory): Category
    {
        return new Category(
            $eloquentCategory->id,
            $eloquentCategory->name,
            $eloquentCategory->description,
            $eloquentCategory->created_at,
            $eloquentCategory->updated_at
        );
    }
}
```

---

## 🗄️ PASO 9: Crear Migraciones

### 9.1 Crear migración para productos
```bash
php artisan make:migration create_products_table
```

```php
// database/migrations/xxxx_create_products_table.php
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description');
        $table->decimal('price', 10, 2);
        $table->integer('stock');
        $table->string('image');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}
```

### 9.2 Crear migración para categorías
```bash
php artisan make:migration create_categories_table
```

```php
// database/migrations/xxxx_create_categories_table.php
public function up(): void
{
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description');
        $table->timestamps();
    });
}
```

### 9.3 Crear migración para relación N:N productos-categorías
```bash
php artisan make:migration create_category_product_table
```

```php
// database/migrations/xxxx_create_category_product_table.php
public function up(): void
{
    Schema::create('category_product', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->foreignId('product_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}
```

---

## 🛣️ PASO 10: Definir Rutas API

### 10.1 Rutas en routes/api.php
```php
<?php

use App\Infrastructure\Http\Controllers\AuthController;
use App\Infrastructure\Http\Controllers\ProductController;
use App\Infrastructure\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Productos
    Route::apiResource('products', ProductController::class);
    Route::get('/user/products', [ProductController::class, 'userProducts']);
    
    // Categorías
    Route::apiResource('categories', CategoryController::class);
    
    // Relación productos-categorías
    Route::post('/products/{product}/categories', [ProductController::class, 'attachCategories']);
    Route::delete('/products/{product}/categories/{category}', [ProductController::class, 'detachCategory']);
});
```

---

## 🔧 PASO 11: Configurar Service Provider

### 11.1 AppServiceProvider (app/Providers/AppServiceProvider.php)
```php
<?php

namespace App\Providers;

use App\Domain\Repositories\UserRepository;
use App\Domain\Repositories\ProductRepository;
use App\Domain\Repositories\CategoryRepository;
use App\Domain\Services\UserService;
use App\Domain\Services\ProductService;
use App\Domain\Services\CategoryService;
use App\Application\Ports\UserInputPort;
use App\Application\Ports\ProductInputPort;
use App\Application\Ports\CategoryInputPort;
use App\Application\UseCases\UserUseCase;
use App\Application\UseCases\ProductUseCase;
use App\Application\UseCases\CategoryUseCase;
use App\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\Infrastructure\Persistence\Repositories\EloquentProductRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Services\EloquentUserService;
use App\Infrastructure\Persistence\Services\EloquentProductService;
use App\Infrastructure\Persistence\Services\EloquentCategoryService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings de repositorios (Puertos de Salida)
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepository::class, EloquentCategoryRepository::class);
        
        // Bindings de servicios (Puertos de Entrada)
        $this->app->bind(UserService::class, EloquentUserService::class);
        $this->app->bind(ProductService::class, EloquentProductService::class);
        $this->app->bind(CategoryService::class, EloquentCategoryService::class);
        
        // Bindings de casos de uso (Puertos de Entrada)
        $this->app->bind(UserInputPort::class, UserUseCase::class);
        $this->app->bind(ProductInputPort::class, ProductUseCase::class);
        $this->app->bind(CategoryInputPort::class, CategoryUseCase::class);
    }
}
```

---

## 🚀 PASO 12: Ejecutar y Probar

### 12.1 Ejecutar migraciones
```bash
php artisan migrate
```

### 12.2 Crear seeders para datos de prueba
```bash
php artisan make:seeder UserSeeder
php artisan make:seeder CategorySeeder
```

### 12.3 Probar endpoints con Postman/Insomnia
- POST /api/register
- POST /api/login
- GET /api/products (con token)
- POST /api/products (con token)
- etc.

---

## 📚 Estructura Final del Proyecto COMPLETA

```
app/
├── Domain/                    # Capa de Dominio
│   ├── Entities/             # Entidades de dominio
│   │   ├── User.php
│   │   ├── Product.php
│   │   └── Category.php
│   ├── Repositories/         # PUERTOS DE SALIDA
│   │   ├── UserRepository.php
│   │   ├── ProductRepository.php
│   │   └── CategoryRepository.php
│   ├── Services/             # PUERTOS DE ENTRADA
│   │   └── UserService.php
│   ├── ValueObjects/         # Objetos de valor
│   │   ├── Email.php
│   │   ├── Price.php
│   │   └── Stock.php
│   └── Events/               # Eventos de dominio
│       ├── UserRegistered.php
│       └── ProductUpdated.php
├── Application/               # Capa de Aplicación
│   ├── UseCases/             # CASOS DE USO (PUERTOS DE ENTRADA)
│   │   ├── UserUseCase.php
│   │   ├── ProductUseCase.php
│   │   └── CategoryUseCase.php
│   ├── DTOs/                 # Objetos de transferencia
│   │   └── UserDTO.php
│   ├── Commands/             # Comandos (CQRS)
│   ├── Queries/              # Consultas (CQRS)
│   └── Ports/                # PUERTOS DE ENTRADA
│       └── UserInputPort.php
├── Infrastructure/            # Capa de Infraestructura
│   ├── Persistence/          # Persistencia de datos
│   │   ├── Eloquent/         # Modelos Eloquent
│   │   ├── Repositories/     # ADAPTADORES DE SALIDA
│   │   └── Services/         # Servicios de persistencia
│   ├── Http/                 # ADAPTADORES DE ENTRADA
│   │   ├── Controllers/      # Controladores HTTP
│   │   ├── Requests/         # Validaciones de entrada
│   │   ├── Resources/        # Transformación de respuesta
│   │   └── Middleware/       # Middleware personalizado
│   ├── External/             # Servicios externos
│   └── Messaging/            # Mensajería
└── Shared/                   # Código Compartido
    ├── Exceptions/           # Excepciones personalizadas
    ├── Events/               # Eventos compartidos
    └── Utils/                # Utilidades
```

---

## 🎯 **PUERTOS Y ADAPTADORES EXPLICADOS:**

### **📥 PUERTOS DE ENTRADA (Input Ports):**
1. **UserInputPort** → Define cómo se registran/autentican usuarios
2. **ProductInputPort** → Define cómo se gestionan productos
3. **CategoryInputPort** → Define cómo se gestionan categorías

### **📤 PUERTOS DE SALIDA (Output Ports):**
1. **UserRepository** → Define cómo se persisten usuarios
2. **ProductRepository** → Define cómo se persisten productos
3. **CategoryRepository** → Define cómo se persisten categorías

### **🔌 ADAPTADORES DE ENTRADA (Input Adapters):**
1. **AuthController** → Adapta peticiones HTTP a casos de uso
2. **ProductController** → Adapta peticiones HTTP a casos de uso
3. **CategoryController** → Adapta peticiones HTTP a casos de uso

### **🔌 ADAPTADORES DE SALIDA (Output Adapters):**
1. **EloquentUserRepository** → Implementa UserRepository con Eloquent
2. **EloquentProductRepository** → Implementa ProductRepository con Eloquent
3. **EloquentCategoryRepository** → Implementa CategoryRepository con Eloquent

---

## 🎯 Beneficios de esta Arquitectura Hexagonal COMPLETA

1. **Separación de Responsabilidades**: Cada capa tiene una responsabilidad específica
2. **Testabilidad**: Fácil de testear cada capa por separado
3. **Mantenibilidad**: Cambios en una capa no afectan a las otras
4. **Escalabilidad**: Fácil agregar nuevas funcionalidades
5. **Independencia de Frameworks**: El dominio no depende de Laravel
6. **Inversión de Dependencias**: Las dependencias apuntan hacia el dominio
7. **Eventos de Dominio**: Permite desacoplar lógica de negocio
8. **Puertos y Adaptadores**: Clara separación entre interfaces e implementaciones

---

## 🔍 Próximos Pasos

1. Implementar validaciones más robustas
2. Agregar manejo de errores centralizado
3. Implementar logging y monitoreo
4. Agregar tests unitarios y de integración
5. Implementar cache para mejorar rendimiento
6. Agregar documentación con Swagger/OpenAPI
7. Implementar CQRS (Command Query Responsibility Segregation)
8. Agregar Event Sourcing para auditoría
