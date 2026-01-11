<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { 
    PrinterIcon, 
    BanknotesIcon, 
    ChevronLeftIcon,
    BuildingOfficeIcon,
    FolderIcon,
    CalendarIcon,
    CreditCardIcon,
    TrashIcon
} from '@heroicons/vue/24/outline';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps({
    invoice: Object,
});

const showPaymentModal = ref(false);

const paymentForm = useForm({
    amount: '',
    payment_date: new Date().toISOString().substr(0, 10),
    method: 'Transferencia Bancaria',
    reference: '',
});

const maxPayment = computed(() => Number(props.invoice.balance_due));

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'UTC' });
};

const submitPayment = () => {
    paymentForm.post(route('invoices.payments.store', props.invoice.id), {
        onSuccess: () => {
            showPaymentModal.value = false;
            paymentForm.reset();
        }
    });
};

const printInvoice = () => {
    window.print();
};

const deleteInvoice = () => {
    if (confirm('¿Estás seguro de eliminar esta factura? Esta acción no se puede deshacer.')) {
        router.delete(route('invoices.destroy', props.invoice.id));
    }
};

const statusConfig = {
    draft: { label: 'Borrador', class: 'bg-gray-100 text-gray-700' },
    sent: { label: 'Enviada', class: 'bg-blue-100 text-blue-700' },
    partial: { label: 'Parcial', class: 'bg-yellow-100 text-yellow-700' },
    paid: { label: 'Pagada', class: 'bg-green-100 text-green-700' },
    overdue: { label: 'Vencida', class: 'bg-red-100 text-red-700' },
};
</script>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }
    .print-container {
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    @page { margin: 0; }
    body { 
        -webkit-print-color-adjust: exact; 
        print-color-adjust: exact;
    }
}
</style>

<template>
    <Head :title="`Factura ${invoice.number}`" />

    <AuthenticatedLayout>
        <!-- Custom Header for Show View -->
        <template #header>
            <div class="w-full flex flex-col md:flex-row md:items-center gap-4 no-print">
                <div class="flex-1 flex items-center gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold leading-tight text-gray-900">{{ invoice.number }}</h2>
                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', statusConfig[invoice.status]?.class || 'bg-gray-100 text-gray-800']">
                                {{ statusConfig[invoice.status]?.label || invoice.status }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 mt-1 text-sm text-gray-500">
                             <span class="flex items-center gap-1"><CalendarIcon class="w-4 h-4"/> {{ formatDate(invoice.date) }}</span>
                             <span v-if="invoice.project" class="flex items-center gap-1"><FolderIcon class="w-4 h-4"/> {{ invoice.project.name }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button v-if="invoice.status !== 'paid' && Number(invoice.balance_due) >= Number(invoice.total)" @click="deleteInvoice" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest hover:bg-red-50 focus:outline-none transition ease-in-out duration-150">
                        <TrashIcon class="w-4 h-4 mr-2" />
                        Eliminar
                    </button>
                    <button @click="printInvoice" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150">
                        <PrinterIcon class="w-4 h-4 mr-2" />
                        Imprimir
                    </button>
                    <button v-if="invoice.balance_due > 0" @click="showPaymentModal = true" class="inline-flex items-center px-4 py-2 bg-brand border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-600 focus:outline-none transition ease-in-out duration-150">
                        <BanknotesIcon class="w-4 h-4 mr-2" />
                        Registrar Pago
                    </button>
                </div>
            </div>
        </template>

        <div class="py-0">
             <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start ">
                
                <!-- Left Column: The Invoice Document -->
                <div class="lg:col-span-2 border border-gray-100">
                    <div class="bg-white shadow-lg rounded-none sm:rounded-lg print-container relative min-h-[800px] flex flex-col">
                        
                        <!-- Watermark -->
                        <div v-if="invoice.status === 'paid'" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-10 no-print">
                            <span class="transform -rotate-45 text-9xl font-black text-green-600 border-8 border-green-600 p-4 rounded-xl uppercase">PAGADO</span>
                        </div>

                        <!-- Brand Header -->
                        <div class="p-8 border-b-2 border-brand/20 flex justify-between items-center bg-gradient-to-r from-gray-50 to-white">
                            <div>
                                <h1 class="text-4xl font-black text-gray-900 tracking-tight">FACTURA</h1>
                                <p class="text-base font-mono text-brand mt-2 font-semibold"># {{ invoice.number }}</p>
                            </div>
                            <div class="text-right">
                                <!-- Company Logo & Info -->
                                <img src="/images/logo-dark.png" alt="GrinTic" class="h-12 w-auto ml-auto mb-3" />
                                <div class="text-xs text-gray-500 space-y-0.5">
                                    <div class="font-medium text-gray-700">RUT: 1121854219</div>
                                    <div>Bogotá, Colombia</div>
                                    <div class="text-brand font-medium">www.grintic.com</div>
                                </div>
                            </div>
                        </div>

                        <!-- Client & Dates -->
                        <div class="p-8 grid grid-cols-2 gap-8 print-spacing">
                            <div>
                                <h4 class="text-xs uppercase font-bold text-gray-400 mb-2">Facturar a</h4>
                                <h3 class="font-bold text-gray-900 text-lg">{{ invoice.company?.name }}</h3>
                                <p class="text-sm text-gray-600">{{ invoice.company?.tax_id }}</p>
                                <p class="text-sm text-gray-600" v-if="invoice.project">Ref: {{ invoice.project.name }}</p>
                            </div>
                            <div class="text-right space-y-2">
                                <div class="flex justify-between border-b border-gray-100 pb-1">
                                    <span class="text-sm text-gray-500">Fecha Emisión:</span>
                                    <span class="font-medium text-gray-900">{{ formatDate(invoice.date) }}</span>
                                </div>
                                <div class="flex justify-between border-b border-gray-100 pb-1">
                                    <span class="text-sm text-gray-500">Vencimiento:</span>
                                    <span class="font-medium text-gray-900">{{ formatDate(invoice.due_date) }}</span>
                                </div>
                                <div class="flex justify-between pt-1">
                                    <span class="text-sm text-gray-500">Moneda:</span>
                                    <span class="font-medium text-gray-900">{{ invoice.currency }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="flex-grow px-8 print-section-spacing">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b-2 border-brand">
                                        <th class="text-left py-3 font-bold text-gray-800 text-sm">Descripción</th>
                                        <th class="text-right py-3 font-bold text-gray-800 text-sm w-20">Cant.</th>
                                        <th class="text-right py-3 font-bold text-gray-800 text-sm w-32">Precio</th>
                                        <th class="text-right py-3 font-bold text-gray-800 text-sm w-32">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in invoice.items" :key="item.id" class="border-b border-gray-100">
                                        <td class="py-4 text-sm text-gray-700">{{ item.description }}</td>
                                        <td class="py-4 text-sm text-gray-700 text-right">{{ item.quantity }}</td>
                                        <td class="py-4 text-sm text-gray-700 text-right">{{ formatCurrency(item.price) }}</td>
                                        <td class="py-4 text-sm text-gray-900 font-bold text-right">{{ formatCurrency(item.total) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer Totals -->
                        <div class="p-8 bg-gray-50 border-t border-gray-200 print-section-spacing">
                            <div class="flex justify-between items-start gap-8">
                                <!-- Notes/Terms Column -->
                                <div class="flex-1 max-w-sm print-notes-spacing">
                                    <h4 class="text-xs uppercase font-bold text-gray-400 mb-2">Notas</h4>
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        Gracias por confiar en GrinTic. El pago se considera recibido una vez confirmado en nuestra cuenta bancaria. Para cualquier consulta sobre esta factura, contáctenos.
                                    </p>
                                </div>
                                <!-- Totals Column -->
                                <div class="w-56 space-y-1">
                                    <div class="flex justify-between text-sm text-gray-600 py-1">
                                        <span>Subtotal</span>
                                        <span class="font-medium">{{ formatCurrency(invoice.total) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm text-gray-500 py-1">
                                        <span>IVA (0%)</span>
                                        <span>$0.00</span>
                                    </div>
                                    <div class="flex justify-between text-base font-bold text-gray-900 border-t-2 border-gray-300 pt-3 mt-2">
                                        <span>TOTAL</span>
                                        <span>{{ formatCurrency(invoice.total) }}</span>
                                    </div>
                                    <div v-if="invoice.balance_due < invoice.total" class="flex justify-between text-sm text-gray-600 pt-2 no-print">
                                        <span>Abonado</span>
                                        <span>{{ formatCurrency(invoice.total - invoice.balance_due) }}</span>
                                    </div>
                                    <div v-if="invoice.balance_due > 0" class="flex justify-between text-sm font-semibold text-gray-800 border-t border-gray-200 pt-2 mt-1">
                                        <span>Saldo Pendiente</span>
                                        <span>{{ formatCurrency(invoice.balance_due) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Footer -->
                        <div class="px-8 py-4 border-t border-gray-100 bg-white text-center">
                            <p class="text-xs text-gray-400">
                                <span class="font-semibold text-gray-500">GrinTic</span> · Desarrollo de Software & Servicios Cloud
                            </p>
                            <p class="text-xs text-brand font-medium mt-1">
                                www.grintic.com · clientes@grintic.com
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Right Column: Sidebar info (No Print) -->
                <div class="lg:col-span-1 space-y-6 no-print">
                    
                    <!-- Client Card -->
                    <div class="bg-white p-5 rounded-xl shadow-sm ring-1 ring-gray-900/5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                                <BuildingOfficeIcon class="h-5 w-5"/>
                            </div>
                            <h3 class="font-semibold text-gray-900">Cliente</h3>
                        </div>
                        <div class="text-sm">
                            <p class="font-medium text-gray-800">{{ invoice.company?.name }}</p>
                            <p class="text-gray-500">{{ invoice.company?.tax_id }}</p>
                        </div>
                    </div>

                    <!-- Project Card -->
                     <div v-if="invoice.project" class="bg-white p-5 rounded-xl shadow-sm ring-1 ring-gray-900/5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                                <FolderIcon class="h-5 w-5"/>
                            </div>
                            <h3 class="font-semibold text-gray-900">Proyecto</h3>
                        </div>
                        <div class="text-sm">
                            <Link :href="route('projects.show', invoice.project.id)" class="font-medium text-brand hover:underline">
                                {{ invoice.project.name }}
                            </Link>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-brand h-1.5 rounded-full" :style="{ width: invoice.project.progress + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History (Master Invoice Flow) -->
                    <div class="bg-white p-5 rounded-xl shadow-sm ring-1 ring-gray-900/5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                                    <CreditCardIcon class="h-5 w-5"/>
                                </div>
                                <h3 class="font-semibold text-gray-900">Historial Pagos</h3>
                            </div>
                            <span class="text-xs font-medium bg-gray-100 px-2 py-0.5 rounded-full">{{ invoice.payments?.length || 0 }}</span>
                        </div>

                        <div v-if="invoice.payments && invoice.payments.length > 0" class="space-y-4">
                            <div v-for="payment in invoice.payments" :key="payment.id" class="relative pl-4 border-l-2 border-gray-200">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ formatCurrency(payment.amount) }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(payment.payment_date) }}</p>
                                    </div>
                                    <span class="text-[10px] uppercase font-bold text-gray-400">{{ payment.method }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-gray-400 text-sm">
                            No hay pagos registrados aún.
                        </div>

                        <div v-if="invoice.balance_due > 0" class="mt-4 pt-4 border-t border-gray-100">
                             <button @click="showPaymentModal = true" class="w-full text-center text-sm font-semibold text-brand hover:text-brand-700">
                                + Registrar Nuevo Abono
                             </button>
                        </div>
                    </div>

                </div>

             </div>
        </div>

        <!-- Payment Modal (Same as before) -->
        <Modal :show="showPaymentModal" @close="showPaymentModal = false">
             <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Registrar Nuevo Pago</h2>
                <form @submit.prevent="submitPayment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Monto a Pagar</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" step="0.01" v-model="paymentForm.amount" :max="maxPayment" class="block w-full rounded-md border-gray-300 pl-7 pr-12 focus:border-brand focus:ring-brand sm:text-sm" placeholder="0.00" required>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-gray-500 sm:text-sm">USD</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Máximo permitido: {{ formatCurrency(maxPayment) }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha de Pago</label>
                        <input type="date" v-model="paymentForm.payment_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Método de Pago</label>
                        <CustomSelect
                            v-model="paymentForm.method"
                            :options="[
                                { value: 'Transferencia Bancaria', label: 'Transferencia Bancaria' },
                                { value: 'Efectivo', label: 'Efectivo' },
                                { value: 'Cheque', label: 'Cheque' },
                                { value: 'Tarjeta de Crédito', label: 'Tarjeta de Crédito' },
                                { value: 'Otro', label: 'Otro' },
                            ]"
                            class="mt-1"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Referencia (Opcional)</label>
                        <input type="text" v-model="paymentForm.reference" placeholder="Nro de comprobante..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="showPaymentModal = false">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="paymentForm.processing">Registrar Pago</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
