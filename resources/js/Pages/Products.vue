<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

import { ref, reactive, onMounted, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

const form = useForm({
    name: '',
    markup: '',
});

// если редактируем
let isEdit = ref(false);
let isEditId = ref(0);
// открыто ли модальное окно 
let isOpenModal = ref(false);
// сообщение от сервера
let messageResponse = ref('');
let messageResponseColor = ref('');
// загруженны ли данные с сервера
let isLoaded = ref(false);

function closemessageResponse(){
    isOpenModal.value = false;
    messageResponse.value = '';
    messageResponseColor.value = '';
}

const products = reactive({})

function  getProducts(){
    axios.get(route('products.index'))
        .then(response => {
            products.value = response.data.products;
            isLoaded.value = true;
    })
}
getProducts();


function updateTable(mes, isok){
    isEdit = false;
    isEditId = 0;
    isOpenModal.value = true;
    messageResponse.value = mes;
    if (isok) {
        messageResponseColor.value = 'mgreen';
    } else {
        messageResponseColor.value = 'mred';
    }
    
    getProducts();
    setTimeout(closemessageResponse, 2000);
}
function updateProducts(ids){
    let b = products.value.find((el) => el.id == ids);
    form.name = b.name;
    form.markup = b.markup;
    isEdit.value = true;
    isEditId.value = ids;
}

function clearProducts(){
    form.name = '';
    form.markup = '';
}

function cancelEdit() {
    isEdit.value = false;
    isEditId.value = 0;
    form.reset();
}

function deleteProducts(ids){
    let a = confirm('Вы действительно хотите удалить запись?');
    if (a == true) {
        axios.delete(route('products.delete', ids))
            .then(response => {
                isOpenModal.value = true;
                messageResponse.value = response.data.status
                getProducts();
                setTimeout(closemessageResponse, 2000);
            })
    }
}

const selectedProduct = ref(null);
const showModal = ref(false);

function openProductDetails(productId) {
    axios.get(route('products.details', productId))
        .then(response => {
            selectedProduct.value = response.data.product;
            showModal.value = true;
        })
        .catch(error => {
            console.error('Ошибка при получении деталей продукта:', error);
        });
}

function closeModal() {
    showModal.value = false;
    selectedProduct.value = null;
}

const searchQuery = ref('');
const sortBy = ref('name');
const sortOrder = ref('asc');

const minPrice = ref('');
const maxPrice = ref('');

// Сортировка и поиск
// const filteredAndSortedProducts = computed(() => {
//     let filtered = products.value.filter(product => 
//         product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) &&
//         (minPrice.value === '' || product.total_price >= parseFloat(minPrice.value)) &&
//         (maxPrice.value === '' || product.total_price <= parseFloat(maxPrice.value))
//     );
    
//     filtered.sort((a, b) => {
//         let modifier = sortOrder.value === 'asc' ? 1 : -1;
//         if (a[sortBy.value] < b[sortBy.value]) return -1 * modifier;
//         if (a[sortBy.value] > b[sortBy.value]) return 1 * modifier;
//         return 0;
//     });
    
//     return filtered;
// });

// function toggleSort(column) {
//     if (sortBy.value === column) {
//         sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
//     } else {
//         sortBy.value = column;
//         sortOrder.value = 'asc';
//     }
// }

const errors = ref({});

function validateForm() {
    errors.value = {};
    if (!form.name) errors.value.name = 'Название продукта обязательно';
    if (!form.markup) errors.value.markup = 'Наценка обязательна';
    if (isNaN(form.markup) || form.markup < 0) errors.value.markup = 'Наценка должна быть положительным числом';
    return Object.keys(errors.value).length === 0;
}

function submitForm() {
    if (validateForm()) {
        const url = isEdit.value ? route('products.update', isEditId.value) : route('products.store');
        const data = {
            name: form.name,
            markup: form.markup
        };

        axios.post(url, data)
            .then(response => {
                if (response.data.flash?.message) {
                    isOpenModal.value = true;
                    messageResponse.value = response.data.flash.message;
                    messageResponseColor.value = 'mgreen';
                    setTimeout(closemessageResponse, 2000);
                }
                form.reset();
                if (isEdit.value) {
                    isEdit.value = false;
                    isEditId.value = 0;
                }
                getProducts();
            })
            .catch(error => {
                console.error(error);
                if (error.response?.data?.errors) {
                    errors.value = error.response.data.errors;
                }
            });
    }
}

const editingProduct = ref(null);

// function startEditing(product) {
//     editingProduct.value = { ...product };
// }

// function cancelEditing() {
//     editingProduct.value = null;
// }

// function saveEdit() {
//     if (validateForm(editingProduct.value)) {
//         axios.put(`/update-product/${editingProduct.value.id}`, editingProduct.value)
//             .then(response => {
//                 getProducts();
//                 editingProduct.value = null;
//             })
//             .catch(error => {
//                 console.error('Error updating product:', error);
//             });
//     }
// }

</script>

<template>
    <AppLayout title="Products">
        <div v-if = "!isLoaded"  class="preload">
            <div class="preload2"></div>
        </div>
        <div class="modalMessage" :class="messageResponseColor" v-if="isOpenModal">{{ messageResponse }}</div>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Продукты
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    
                    
                  
                    <h3 class="text-lg font-medium mb-4">Добавить новый продукт</h3>
                    <form @submit.prevent="submitForm">
                        <input type="hidden" name="_token" :value="csrf">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Название продукта</label>
                            <input type="text" id="name" v-model="form.name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                            <p v-if="errors.name" class="mt-2 text-sm text-red-600">{{ errors.name }}</p>
                        </div>
                        <div class="mb-4">
                            <label for="markup" class="block text-sm font-medium text-gray-700">Наценка (%)</label>
                            <input type="number" id="markup" v-model="form.markup" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-500 focus:border-blue-500">
                            <p v-if="errors.markup" class="mt-2 text-sm text-red-600">{{ errors.markup }}</p>
                        </div>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ isEdit ? 'Обновить продукт' : 'Добавить продукт' }}
                        </button>
                        <button v-if="isEdit" type="button" @click="cancelEdit" class="ml-2 inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Отменить
                        </button>
                    </form>
                    
                    

                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-5">
                    <h3 class="text-lg font-medium mb-4">Список поставщиков</h3>
                    



<table v-if = "isLoaded" class="min-w-full divide-y divide-gray-200 overflow-x-auto">
    <thead class="bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Название
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Наценка
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Себестоимость
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Итоговая цена
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Действия
            </th>
        </tr>
    </thead>
    
        
 
    <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="item in products.value" :key="item.id">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                    
                    <Link :href="'/get-product-by-id/'+item.id" >
                        {{ item.name }}
                    </Link>
                    
                
                </div> 
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ item.markup }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ item.price }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ item.total_price.toFixed(2) }}</div>
            </td>
           
            <td class="px-6 py-4 whitespace-nowrap  text-sm font-medium">
                <a @click="updateProducts(item.id)" :data="item.id" class="text-indigo-600 hover:text-indigo-900">Редактировать</a>
                <a @click="deleteProducts(item.id)" :data="item.id" class="ml-2 text-red-600 hover:text-red-900">Удалить</a>
            </td>
        </tr>
        </tbody>
        </table>   

                </div>
            </div>
        </div>
        <div v-if="selectedProduct" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="my-modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">{{ selectedProduct.name }}</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500">
                            Номенклатура:
                            <span v-for="nomenclature in selectedProduct.nomenclatures" :key="nomenclature.id">
                                {{ nomenclature.name }},
                            </span>
                        </p>
                        <p class="text-sm text-gray-500">
                            Количество: {{ selectedProduct.total_quantity }}
                        </p>
                        <p class="text-sm text-gray-500">
                            Общая цена номенклатуры: {{ selectedProduct.total_nomenclature_price }}
                        </p>
                        <p class="text-sm text-gray-500">
                            Себестоимость продукта: {{ selectedProduct.cost_price }}
                        </p>
                        <p class="text-sm text-gray-500">
                            Стоимость продукта с наценкой: {{selectedProduct.total_price}} 
                        </p>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button id="ok-btn" @click="closeModal" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
