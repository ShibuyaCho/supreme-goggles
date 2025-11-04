{{-- resources/views/pos/modals/product-details.blade.php --}}
<div x-data="{ openProduct:false, product:{} }"
     x-cloak x-show="openProduct"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
  <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
    <div class="flex items-start justify-between">
      <h2 class="text-lg font-semibold">Product Details</h2>
      <button class="text-gray-500 hover:text-gray-700" @click="openProduct=false">&times;</button>
    </div>

    <div class="mt-4 space-y-2 text-sm text-gray-700">
      <p><span class="font-semibold">Name:</span> <span x-text="product.name"></span></p>
      <p><span class="font-semibold">Category:</span> <span x-text="product.category"></span></p>
      <p><span class="font-semibold">Price:</span> $<span x-text="product.price"></span></p>
      <p><span class="font-semibold">THC:</span> <span x-text="product.thc"></span>%</p>
      <p><span class="font-semibold">CBD:</span> <span x-text="product.cbd"></span>%</p>
      <p><span class="font-semibold">Weight:</span> <span x-text="product.weight"></span>g</p>
      <p><span class="font-semibold">Strain:</span> <span x-text="product.strain"></span></p>
      <p><span class="font-semibold">Room:</span> <span x-text="product.room"></span></p>
      <p><span class="font-semibold">Tax Status:</span>
        <span x-text="product.is_untaxed ? 'Untaxed' : 'Taxed'"></span>
      </p>
      <p><span class="font-semibold">GLS Product:</span>
        <span x-text="product.is_gls ? 'Yes' : 'No'"></span>
      </p>
    </div>

    <div class="mt-5 flex items-center justify-end gap-3">
      <button class="rounded-lg border px-4 py-2" @click="openProduct=false">Close</button>
      <button class="rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700"
              @click="addToCart(product.id)">
        Add to Cart
      </button>
    </div>
  </div>
</div>

@once
<script>
  // Use this when clicking on a product tile/card
  window.showProductDetails = async function (productId) {
    try {
      const res = await fetch(`/api/products/${productId}`);
      const data = await res.json();
      const modalRoot = document.querySelector('[x-data*="openProduct"]') || document.querySelector('[x-data]');
      if (modalRoot && modalRoot.__x) {
        modalRoot.__x.$data.product = data;
        modalRoot.__x.$data.openProduct = true;
      }
    } catch (e) {
      console.error(e);
      alert('Could not fetch product details.');
    }
  };

  // Add product to cart directly (simple API call)
  window.addToCart = async function (productId) {
    try {
      const res = await fetch(`/pos/cart/add/${productId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });
      const data = await res.json();
      alert(data.message || 'Added to cart.');
      window.location.reload();
    } catch (e) {
      console.error(e);
      alert('Error adding product to cart.');
    }
  };
</script>
@endonce
