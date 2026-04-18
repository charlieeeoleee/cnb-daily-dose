export const products = [
  {
    id: 1,
    name: 'Caramel Iced Coffee',
    category: 'Iced Coffee',
    description: 'Smooth coffee with rich caramel flavor.',
    basePrice: 89,
    image: 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=800&q=80',
    type: 'drink'
  },
  {
    id: 2,
    name: 'Vanilla Latte',
    category: 'Latte',
    description: 'Creamy latte with sweet vanilla notes.',
    basePrice: 95,
    image: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80',
    type: 'drink'
  },
  {
    id: 3,
    name: 'Spanish Latte',
    category: 'Latte',
    description: 'Bold espresso with creamy sweet milk.',
    basePrice: 99,
    image: 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=80',
    type: 'drink'
  },
  {
    id: 4,
    name: 'Butterscotch Coffee',
    category: 'Flavored Coffee',
    description: 'A sweet and buttery coffee favorite.',
    basePrice: 95,
    image: 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=800&q=80',
    type: 'drink'
  },
  {
    id: 5,
    name: 'Iced Americano',
    category: 'Americano',
    description: 'Refreshing black coffee over ice.',
    basePrice: 79,
    image: 'https://images.unsplash.com/photo-1494314671902-399b18174975?auto=format&fit=crop&w=800&q=80',
    type: 'drink'
  },
  {
    id: 6,
    name: 'Cheesy Nachos',
    category: 'Snacks',
    description: 'Crunchy nachos with cheesy goodness.',
    basePrice: 65,
    image: 'https://images.unsplash.com/photo-1513456852971-30c0b8199d4d?auto=format&fit=crop&w=800&q=80',
    type: 'snack'
  }
];

export const sizeOptions = [
  { label: '250ml', price: 0 },
  { label: '350ml', price: 20 },
  { label: '500ml', price: 35 }
];

export const sweetnessOptions = ['0%', '25%', '50%', '75%', '100%'];

export const iceOptions = ['No Ice', 'Less Ice', 'Normal Ice', 'Extra Ice'];

export const addonOptions = [
  { label: 'Extra Espresso Shot', price: 20 },
  { label: 'Extra Syrup', price: 10 },
  { label: 'Whipped Cream', price: 15 }
];