import template from './sw-customer-detail-ict-order-list.html.twig';
import './sw-customer-detail-ict-order-list.scss';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('sw-customer-detail-ict-order-list', {
    template,

    inject: ['repositoryFactory'],

    props: {
        customer: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            orderLists: null,
            isLoading: false,
            expandedItems: [],
            productNames: {},
            pagination: {},
            pageSize: 100
        };
    },

    computed: {
        orderListRepository() {
            return this.repositoryFactory.create('ictech_order_list');
        },
        productRepository() {
            return this.repositoryFactory.create('product');
        },
    },

    created() {
        this.loadOrderLists();
    },

    methods: {
        async loadOrderLists() {
            this.isLoading = true;
            try {
                const criteria = new Criteria(1, 50);
                criteria.addAssociation('customer');
                criteria.addAssociation('translations');
                criteria.addAssociation('products');
                criteria.addAssociation('products.product');
                criteria.addAssociation('products.product.parent');
                criteria.addAssociation('products.product.options.group');
                criteria.addFilter(Criteria.equals('customerId', this.customer.id));
                const orderLists = await this.orderListRepository.search(criteria);
                
                // Preload product names
                for (const orderList of orderLists) {
                    if (orderList.products) {
                        for (const productItem of orderList.products) {
                            if (productItem && productItem.id) {
                                this.productNames[productItem.id] = await this.buildProductName(productItem);
                            }
                        }
                    }
                }
                
                this.orderLists = orderLists;
            } catch (error) {
                console.error('Error loading order lists:', error);
            } finally {
                this.isLoading = false;
            }
        },

        toggleAccordion(id) {
            const index = this.expandedItems.indexOf(id);
            if (index > -1) {
                this.expandedItems.splice(index, 1);
            } else {
                this.expandedItems.push(id);
            }
        },

        isExpanded(id) {
            return this.expandedItems.includes(id);
        },

        getCurrentPage(orderListId) {
            return this.pagination[orderListId] || 1;
        },

        getPaginatedProducts(orderList) {
            const page = this.getCurrentPage(orderList.id);
            const start = (page - 1) * this.pageSize;
            const end = start + this.pageSize;
            return (orderList.products || []).slice(start, end);
        },

        getTotalProducts(orderList) {
            return (orderList.products || []).length;
        },

        onPageChange(orderListId, pageData) {
            if (pageData.limit) {
                this.pageSize = pageData.limit;
            }
            this.pagination = { ...this.pagination, [orderListId]: pageData.page || pageData };
        },

        async buildProductName(productItem) {
            try {
                if (!productItem?.product) return '-';
                
                const product = productItem.product;
                let name = '';
                
                if (product.parentId) {
                    if (product.parent?.name) {
                        name = product.parent.translated?.name || product.parent.name;
                    } else {
                        try {
                            const parentProduct = await this.productRepository.get(product.parentId, Shopware.Context.api);
                            name = parentProduct?.translated?.name || parentProduct?.name || '';
                        } catch (e) {
                            console.error('Error fetching parent product:', e);
                            name = product.translated?.name || product.name || '';
                        }
                    }
                } else {
                    name = product.translated?.name || product.name || '-';
                }
                
                if (product.options?.length > 0) {
                    const variants = product.options.map(option => {
                        const groupName = option.group?.translated?.name || option.group?.name || '';
                        const optionName = option.translated?.name || option.name || '';
                        return groupName && optionName ? `${groupName}: ${optionName}` : optionName;
                    }).filter(Boolean);
                    
                    if (variants.length > 0) {
                        name += ' (' + variants.join(' | ') + ')';
                    }
                }
                
                return name || '-';
            } catch (error) {
                console.error('Error building product name:', error);
                return '-';
            }
        },

        getProductName(productItem) {
            return this.productNames[productItem.id] || '-';
        },
    },
});
