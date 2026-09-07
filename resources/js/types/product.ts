export interface ProductFaq {
    question: string;
    answer: string;
}

export interface ProductImage {
    image: string;
}

export interface EditableProductImage extends ProductImage {
    id: number;
    product_id: number;
    status: string;
    file_path: string;
    is_enabled: boolean;
}
