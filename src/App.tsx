import React, { useState, useEffect, useRef } from 'react';
import { 
  Coffee, IceCream, Leaf, Cake, GlassWater, Egg, ShoppingBag, 
  Search, Plus, Minus, Trash2, Check, ArrowRight, Sun, Moon, 
  LayoutDashboard, Layers, Settings as SettingsIcon, 
  BarChart3, LogOut, ShieldAlert, Printer, FileSpreadsheet, 
  Folder, File, Download, ChevronRight, Play, RefreshCw, 
  ExternalLink, Copy, CheckCircle2, AlertTriangle, Info, Star,
  Pencil, Lock, Upload
} from 'lucide-react';
import JSZip from 'jszip';

// مبدل اعداد انگلیسی به فارسی برای زیبایی ۱۰۰٪ بصری منو و فاکتورها
const toPersianDigits = (num: string | number | undefined | null): string => {
  if (num === undefined || num === null) return '';
  const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
  return num.toString().replace(/\d/g, (x) => persianDigits[parseInt(x)]);
};

// نمایشگر پویای آیکون‌های Font Awesome برای دسته‌بندی‌ها
const renderCategoryIcon = (iconName: string, className = "text-[#c49b63] text-lg") => {
  if (iconName && (iconName.startsWith('fa') || iconName.includes(' '))) {
    return <i className={`${iconName} ${className}`} />;
  }
  switch (iconName) {
    case 'coffee': return <i className={`fa-solid fa-mug-hot ${className}`} />;
    case 'ice-cream': return <i className={`fa-solid fa-ice-cream ${className}`} />;
    case 'leaf': return <i className={`fa-solid fa-leaf ${className}`} />;
    case 'cake': return <i className={`fa-solid fa-cake-candles ${className}`} />;
    case 'glass-water': return <i className={`fa-solid fa-glass-water ${className}`} />;
    case 'egg': return <i className={`fa-solid fa-egg ${className}`} />;
    default: return <i className={`fa-solid fa-mug-hot ${className}`} />;
  }
};

// درون‌ریزی فایل‌های پی‌اچ‌پی از سیستم فایل در زمان بیلد با ویت
// این فیلتر بی‌نظیر ویت، کدهای نوشته شده را به عنوان رشته دریافت می‌کند
const phpFilesRaw = (import.meta as any).glob('../cpanel-php/**/*', { query: '?raw', import: 'default', eager: true }) as Record<string, string>;

// ساختار تمیز نام‌گذاری و مدیریت پوشه‌ها برای نمایش در اکسپلورر کدها
const phpFiles: Record<string, string> = {};
Object.entries(phpFilesRaw).forEach(([path, content]) => {
  const cleanPath = path.replace('../cpanel-php/', '');
  if (cleanPath && !cleanPath.includes('uploads/')) { // عدم نیاز به اینکلود فایل‌های آپلودی خالی
    phpFiles[cleanPath] = content;
  }
});

// دسته‌بندی‌های نمونه اولیه
const INITIAL_CATEGORIES = [
  { id: 1, name: 'قهوه گرم', icon: 'coffee', sort_order: 1 },
  { id: 2, name: 'قهوه سرد', icon: 'ice-cream', sort_order: 2 },
  { id: 3, name: 'دمنوش و چای', icon: 'leaf', sort_order: 3 },
  { id: 4, name: 'کیک و دسر', icon: 'cake', sort_order: 4 },
  { id: 5, name: 'نوشیدنی خنک', icon: 'glass-water', sort_order: 5 },
  { id: 6, name: 'صبحانه و غذا', icon: 'egg', sort_order: 6 }
];

// محصولات نمونه اولیه
const INITIAL_PRODUCTS = [
  { id: 1, category_id: 1, name: 'اسپرسو دوبل', price: 45000, discount: 0, description: 'اسپرسو ۱۰۰٪ عربیکا با طعم عمیق و بادی قوی', ingredients: 'دبل شات قهوه اسپرسو عربیکا', image: 'https://images.unsplash.com/photo-1510972527409-cef5e0af073c?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3', is_available: true, is_popular: true, is_new: false, is_visible: true, sort_order: 1 },
  { id: 2, category_id: 1, name: 'کاپوچینو', price: 55000, discount: 10, description: 'اسپرسو به همراه شیر گرم و فوم غلیظ شیر', ingredients: 'اسپرسو، شیر، فوم شیر، پودر کاکائو', image: 'https://images.unsplash.com/photo-1572442388796-11668a724631?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3', is_available: true, is_popular: false, is_new: false, is_visible: true, sort_order: 2 },
  { id: 3, category_id: 1, name: 'لاته آرت', price: 58000, discount: 0, description: 'ترکیب بی‌نظیر اسپرسو و شیر مخملی با طراحی‌های زیبا', ingredients: 'اسپرسو، شیر، فوم ریز شیر', image: 'https://images.unsplash.com/photo-1541167760496-1628856ab772?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3', is_available: true, is_popular: true, is_new: true, is_visible: true, sort_order: 3 },
  { id: 4, category_id: 2, name: 'آیس لاته', price: 60000, discount: 0, description: 'نسخه خنک قهوه لاته به همراه تکه‌های یخ و فوم شیر', ingredients: 'اسپرسو، شیر سرد، یخ', image: 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3', is_available: true, is_popular: false, is_new: false, is_visible: true, sort_order: 1 },
  { id: 5, category_id: 2, name: 'اسپرسو تانیک', price: 65000, discount: 15, description: 'نوشیدنی گازدار خنک و انرژی‌بخش ترکیبی متمایز', ingredients: 'اسپرسو، آب تونیک، لیمو، یخ', image: 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3', is_available: true, is_popular: true, is_new: true, is_visible: true, sort_order: 2 },
  { id: 6, category_id: 3, name: 'دمنوش آرامش', price: 48000, discount: 0, description: 'ترکیبی معطر و آرامش‌بخش برای کاهش استرس روزانه', ingredients: 'بابونه، بهارنارنج، اسطوخودوس، گل‌گاوزبان', image: 'https://images.unsplash.com/photo-1597481499750-3e6b22637e12?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3', is_available: true, is_popular: false, is_new: false, is_visible: true, sort_order: 1 },
  { id: 7, category_id: 4, name: 'کیک شکلاتی بی‌بی', price: 52000, discount: 0, description: 'کیک شکلاتی کلاسیک فوق‌العاده مرطوب با سس شکلات داغ', ingredients: 'آرد، پودر کاکائو غنی، خامه، سس شکلات', image: 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3', is_available: true, is_popular: true, is_new: false, is_visible: true, sort_order: 1 },
  { id: 8, category_id: 4, name: 'چیزکیک نیویورکی', price: 58000, discount: 5, description: 'چیزکیک پخته غلیظ با کراست بیسکویت و سس تمشک وحشی', ingredients: 'پنیر خامه‌ای، بیسکویت دایجستیو، خامه ترش، تمشک', image: 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3', is_available: true, is_popular: false, is_new: true, is_visible: true, sort_order: 2 },
  { id: 9, category_id: 6, name: 'املت سنتی ایرانی', price: 75000, discount: 0, description: 'املت سنتی ایرانی با گوجه‌فرنگی تازه و نان داغ محلی', ingredients: 'گوجه‌فرنگی تازه، تخم‌مرغ رسمی، کره، ادویه مخصوص', image: 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3', is_available: true, is_popular: true, is_new: false, is_visible: true, sort_order: 1 }
];

// سفارشات اولیه شبیه‌سازی
const INITIAL_ORDERS = [
  {
    order_code: "CAFE-1405-8421",
    customer_name: "امیرحسین رضایی",
    customer_phone: "09121111111",
    order_type: "indoor",
    address: "میدان ونک، پلاک ۳، کافه گالری",
    plaque: "۱۲",
    floor: "همکف",
    unit: "۱",
    description: "کیک شکلاتی با سس شکلات اضافه سرو شود.",
    total_amount: 107000,
    status: "completed",
    created_jalali: "۱۴۰۵/۰۴/۱۵ ۱۲:۳۰:۱۵",
    items: [
      { product_name: "کیک شکلاتی بی‌بی", quantity: 1, price: 52000 },
      { product_name: "کاپوچینو", quantity: 1, price: 55000 }
    ]
  },
  {
    order_code: "CAFE-1405-3210",
    customer_name: "زهرا سادات مدنی",
    customer_phone: "09353333333",
    order_type: "outdoor",
    address: "",
    plaque: "",
    floor: "",
    unit: "",
    description: "آیس لاته بدون شکر باشد.",
    total_amount: 118000,
    status: "preparing",
    created_jalali: "۱۴۰۵/۰۴/۱۵ ۱۲:۱۵:۴۲",
    items: [
      { product_name: "آیس لاته", quantity: 1, price: 60000 },
      { product_name: "چیزکیک نیویورکی", quantity: 1, price: 58000 }
    ]
  }
];

export default function App() {
  // کنترل نمای اصلی برنامه: customer (منوی مشتری)، admin (پنل ادمین)، code (نمایش کدها و دانلود)
  const [appMode, setAppMode] = useState<'customer' | 'admin' | 'code'>('customer');
  
  // سیستم تم همیشه تاریک (حذف لایت مود بنا به درخواست مشتری)
  const [theme, setTheme] = useState<'light' | 'dark'>('dark');

  // داده‌های اصلی سیستم (با همگام‌سازی LocalStorage جهت کارکرد ۱۰۰٪ واقعی)
  const [categories, setCategories] = useState<any[]>(() => {
    const saved = localStorage.getItem('demo_categories');
    return saved ? JSON.parse(saved) : INITIAL_CATEGORIES;
  });
  const [products, setProducts] = useState<any[]>(() => {
    const saved = localStorage.getItem('demo_products');
    return saved ? JSON.parse(saved) : INITIAL_PRODUCTS;
  });
  const [orders, setOrders] = useState<any[]>(() => {
    const saved = localStorage.getItem('mock_orders');
    return saved ? JSON.parse(saved) : INITIAL_ORDERS;
  });
  const [settings, setSettings] = useState<any>(() => {
    const saved = localStorage.getItem('demo_settings');
    const defaultSettings = {
      cafe_name: 'کافه گالری',
      cafe_description: 'فضایی آرام و دلنشین همراه با بهترین طعم‌های قهوه تخصصی و دسرهای دست‌ساز',
      cafe_phone: '021-88888888',
      cafe_address: 'تهران، خیابان ولیعصر، نرسیده به میدان ونک، بن‌بست کافه، پلاک ۱۲',
      working_hours: 'همه‌روزه از ساعت ۸:۰۰ صبح الی ۲۳:۳۰ شب',
      primary_color: '#8B5A2B',
      secondary_color: '#D2B48C',
      instagram_link: 'https://instagram.com/cafegallery',
      telegram_link: 'https://t.me/cafegallery',
      logo_url: '',
      banner_url: '',
      admin_username: 'admin', // نام کاربری پیش‌فرض ادمین
      admin_password: 'admin' // رمز عبور ادمین پیش‌فرض
    };
    if (saved) {
      try {
        const parsed = JSON.parse(saved);
        return { ...defaultSettings, ...parsed };
      } catch (e) {
        return defaultSettings;
      }
    }
    return defaultSettings;
  });

  // افکت‌های هماهنگ‌سازی LocalStorage
  useEffect(() => {
    localStorage.setItem('demo_categories', JSON.stringify(categories));
  }, [categories]);
  useEffect(() => {
    localStorage.setItem('demo_products', JSON.stringify(products));
  }, [products]);
  useEffect(() => {
    localStorage.setItem('mock_orders', JSON.stringify(orders));
  }, [orders]);
  useEffect(() => {
    localStorage.setItem('demo_settings', JSON.stringify(settings));
  }, [settings]);

  // کنترل تم تاریک/روشن
  useEffect(() => {
    localStorage.setItem('theme_preference', theme);
    if (theme === 'dark') {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  }, [theme]);

  // زمان زنده ایران
  const [liveTime, setLiveTime] = useState('');
  useEffect(() => {
    const updateTime = () => {
      const now = new Date();
      const y = 1405; // سال دمو پروپوزال مشتری
      const m = String(now.getMonth() + 1).padStart(2, '0');
      const d = String(now.getDate()).padStart(2, '0');
      const time = now.toTimeString().split(' ')[0];
      setLiveTime(`${y}/${m}/${d} - ${time}`);
    };
    updateTime();
    const interval = setInterval(updateTime, 1000);
    return () => clearInterval(interval);
  }, []);

  // سیستم آدرس‌دهی و مسیریابی زنده (هماهنگ با cPanel و کلاینت)
  useEffect(() => {
    const handleHashChange = () => {
      const hash = window.location.hash;
      if (hash === '#admin') {
        setAppMode('admin');
      } else if (hash === '#code') {
        setAppMode('code');
      } else {
        setAppMode('customer');
      }
    };
    
    // لود اولیه
    handleHashChange();
    
    window.addEventListener('hashchange', handleHashChange);
    return () => window.removeEventListener('hashchange', handleHashChange);
  }, []);

  // -------------------------------------------------------------------
  // ۱. پیاده‌سازی منوی مشتری (Customer Mode)
  // -------------------------------------------------------------------
  const [selectedCategory, setSelectedCategory] = useState<number>(0);
  const [searchQuery, setSearchQuery] = useState('');
  const [sortType, setSortType] = useState<'default' | 'new' | 'popular' | 'discount'>('default');
  const [cart, setCart] = useState<any[]>([]);
  const [isCartOpen, setIsCartOpen] = useState(false);
  const [checkoutStep, setCheckoutStep] = useState(1); // 1: Cart list, 2: Address Form
  const [selectedProductDetail, setSelectedProductDetail] = useState<any | null>(null);

  // فیلدهای فرم ثبت سفارش
  const [orderType, setOrderType] = useState<'indoor' | 'outdoor'>('outdoor');
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [phone, setPhone] = useState('');
  const [address, setAddress] = useState('');
  const [plaque, setPlaque] = useState('');
  const [floor, setFloor] = useState('');
  const [unit, setUnit] = useState('');
  const [description, setDescription] = useState('');

  // فیلتر کردن محصولات در نمای مشتری
  const filteredProducts = products.filter(p => {
    if (!p.is_visible) return false;
    const matchCat = selectedCategory === 0 || p.category_id === selectedCategory;
    const matchSearch = p.name.toLowerCase().includes(searchQuery.toLowerCase());
    
    let matchSort = true;
    if (sortType === 'new') matchSort = p.is_new;
    else if (sortType === 'popular') matchSort = p.is_popular;
    else if (sortType === 'discount') matchSort = p.discount > 0;

    return matchCat && matchSearch && matchSort;
  });

  // محاسبه روند فروش سفارش‌های موفق برای نمودار (روزهای شنبه تا جمعه)
  const getSalesTrendData = () => {
    const weekdayIndexMap: { [key: number]: number } = {
      6: 0, // شنبه
      0: 1, // یکشنبه
      1: 2, // دوشنبه
      2: 3, // سه‌شنبه
      3: 4, // چهارشنبه
      4: 5, // پنجشنبه
      5: 6  // جمعه
    };
    
    const days = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'];
    const dailySales = [120000, 340000, 290000, 480000, 410000, 780000, 950000];
    
    orders.filter(o => o.status === 'completed').forEach(o => {
      if (o.created_jalali) {
        const datePart = o.created_jalali.trim().split(' ')[0];
        const parts = datePart.split('/');
        if (parts.length === 3) {
          const m = parseInt(parts[1]) - 1;
          const d = parseInt(parts[2]);
          const tempDate = new Date(new Date().getFullYear(), m, d);
          const w = tempDate.getDay();
          const index = weekdayIndexMap[w];
          if (index !== undefined) {
            dailySales[index] += parseFloat(o.total_amount || 0);
          }
        }
      }
    });
    
    return days.map((day, idx) => ({
      day,
      sales: dailySales[idx]
    }));
  };

  // افزودن به سبد
  const addToCart = (prod: any) => {
    const finalPrice = prod.price - (prod.price * prod.discount / 100);
    const existing = cart.find(item => item.id === prod.id);
    if (existing) {
      setCart(cart.map(item => item.id === prod.id ? { ...item, quantity: item.quantity + 1 } : item));
    } else {
      setCart([...cart, {
        id: prod.id,
        name: prod.name,
        price: finalPrice,
        image: prod.image,
        discount: prod.discount,
        quantity: 1
      }]);
    }
    showNotification('محصول به سبد خرید اضافه شد.', 'success');
  };

  // حذف و تغییر تعداد سبد
  const updateCartQty = (id: number, change: number) => {
    const existing = cart.find(item => item.id === id);
    if (!existing) return;
    const newQty = existing.quantity + change;
    if (newQty <= 0) {
      setCart(cart.filter(item => item.id !== id));
      showNotification('محصول از سبد خرید حذف شد.', 'info');
    } else {
      setCart(cart.map(item => item.id === id ? { ...item, quantity: newQty } : item));
    }
  };

  // ثبت نهایی سفارش مشتری (ثبت در آرشیو)
  const handleCheckoutSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (cart.length === 0) return;

    // بررسی ولیدیشن شماره همراه (فیلد موبایل در حالت حضوری اجباری است)
    if (orderType === 'indoor' && !/^09[0-9]{9}$/.test(phone)) {
      showNotification('فرمت شماره موبایل نامعتبر است (مثال: 09123456789)', 'error');
      return;
    }

    const orderCode = `CAFE-1405-${Math.floor(1000 + Math.random() * 9000)}`;
    const totalAmount = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);

    const newOrder = {
      order_code: orderCode,
      customer_name: orderType === 'indoor' ? `${firstName} ${lastName}` : firstName,
      customer_phone: orderType === 'indoor' ? phone : '09000000000',
      order_type: orderType,
      address: orderType === 'indoor' ? address : '',
      plaque: orderType === 'indoor' ? plaque : '',
      floor: orderType === 'indoor' ? floor : '',
      unit: orderType === 'indoor' ? unit : '',
      description: description,
      total_amount: totalAmount,
      status: 'registered',
      created_jalali: liveTime.split(' - ')[0] + ' ' + liveTime.split(' - ')[1],
      items: cart.map(item => ({
        product_name: item.name,
        quantity: item.quantity,
        price: item.price
      }))
    };

    // اضافه کردن به دیتابیس ادمین (آرشیو)
    setOrders([newOrder, ...orders]);
    
    // پاک کردن سبد خرید کلاینت
    setCart([]);
    setIsCartOpen(false);
    setCheckoutStep(1);

    alert(`سفارش شما با موفقیت ثبت شد.\n\nکد پیگیری شما: ${orderCode}\nلطفاً جهت تحویل این کد را به همراه داشته باشید.`);
  };

  // پخش صدای زنگ هشدار نوتیفیکیشن ادمین
  const playNotificationSound = () => {
    try {
      const audioCtx = new (window.AudioContext || (window as any).webkitAudioContext)();
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.type = 'triangle';
      osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // نت D5
      osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.15); // نت A5
      gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start();
      osc.stop(audioCtx.currentTime + 0.35);
    } catch (e) {
      console.log('Audio playback blocked by browser');
    }
  };

  // -------------------------------------------------------------------
  // ۲. مدیریت بخش ادمین (Admin Panel Mode)
  // -------------------------------------------------------------------
  const [adminTab, setAdminTab] = useState<'dashboard' | 'products' | 'categories' | 'archive' | 'reports' | 'settings'>('dashboard');
  const [isAdminLoggedIn, setIsAdminLoggedIn] = useState(false); // ورود امن با تأیید اعتبار رمز عبور
  const [adminUser, setAdminUser] = useState('');
  const [adminPass, setAdminPass] = useState('');

  const [adminAlerts, setAdminAlerts] = useState<{ id: string; message: string; code: string }[]>([]);
  
  // ذخیره کدهای سفارش‌های دیده شده توسط ادمین در لوکال استوریج جهت عدم تکرار آلارم پس از ورودهای مکرر
  const [acknowledgedCodes, setAcknowledgedCodes] = useState<string[]>(() => {
    try {
      const saved = localStorage.getItem('acknowledged_order_codes');
      if (saved) return JSON.parse(saved);
      
      // در لود اول، تمامی سفارشات فعلی در دیتابیس را لود کرده و نادیده می‌گیریم تا فقط برای موارد کاملاً جدید پس از شروع برنامه آلارم پخش شود
      const savedOrders = localStorage.getItem('mock_orders');
      const loadedOrders = savedOrders ? JSON.parse(savedOrders) : INITIAL_ORDERS;
      return loadedOrders.map((o: any) => o.order_code);
    } catch (e) {
      return [];
    }
  });

  // ذخیره خودکار کدهای سفارش تایید شده
  useEffect(() => {
    localStorage.setItem('acknowledged_order_codes', JSON.stringify(acknowledgedCodes));
  }, [acknowledgedCodes]);

  // افکت هوشمند تشخیص سفارشات جدید و پخش آلارم صوتی
  useEffect(() => {
    if (appMode === 'admin' && isAdminLoggedIn) {
      const unacknowledged = orders.filter(o => o.status === 'registered' && !acknowledgedCodes.includes(o.order_code));
      if (unacknowledged.length > 0) {
        unacknowledged.forEach(o => {
          playNotificationSound();
          setAdminAlerts(prev => {
            if (prev.some(a => a.id === o.order_code)) return prev;
            return [
              ...prev,
              { id: o.order_code, message: `سفارش جدید از ${o.customer_name} ثبت شد!`, code: o.order_code }
            ];
          });
        });
        
        // ثبت در کدهای تایید شده تا دیگر برای اینها آلارم صوتی یا آلرت پخش نشود
        const newCodes = unacknowledged.map(o => o.order_code);
        setAcknowledgedCodes(prev => {
          const unique = new Set([...prev, ...newCodes]);
          return Array.from(unique);
        });
      }
    }
  }, [orders, appMode, isAdminLoggedIn, acknowledgedCodes]);

  // مارک کردن تمامی سفارشات موجود به عنوان دیده شده به محض ورود ادمین به پنل جهت عدم تکرار هشدارها پس از خروج و ورود مجدد
  useEffect(() => {
    if (appMode === 'admin' && isAdminLoggedIn) {
      const currentCodes = orders.map(o => o.order_code);
      setAcknowledgedCodes(prev => {
        const unique = new Set([...prev, ...currentCodes]);
        return Array.from(unique);
      });
    }
  }, [appMode, isAdminLoggedIn]);

  const handleAdminLogin = (e: React.FormEvent) => {
    e.preventDefault();
    const correctUser = settings.admin_username || 'admin';
    const correctPass = settings.admin_password || 'admin';
    if (adminUser === correctUser && adminPass === correctPass) {
      setIsAdminLoggedIn(true);
      showNotification('خوش آمدید! با موفقیت به پنل مدیریت کافه وارد شدید.', 'success');
    } else {
      showNotification('نام کاربری یا رمز عبور وارد شده اشتباه است!', 'error');
    }
  };

  // آمار ادمین
  const activeOrders = orders.filter(o => o.status !== 'completed' && o.status !== 'cancelled');
  const salesToday = orders.filter(o => o.status !== 'cancelled').reduce((acc, o) => acc + o.total_amount, 0);

  // شبیه‌سازی تزریق سفارش تصادفی برای تست صدای آلارم و بروزرسانی لحظه‌ای
  const handleSimulateNewOrder = () => {
    const randomProduct = products[Math.floor(Math.random() * products.length)];
    const code = `CAFE-1405-${Math.floor(1000 + Math.random() * 9000)}`;
    const names = ['مهدی حسینی', 'سارا رضایی', 'نیما کریمی', 'الناز مرادی', 'امیر عباسی'];
    const selectedName = names[Math.floor(Math.random() * names.length)];
    
    const simOrder = {
      order_code: code,
      customer_name: selectedName,
      customer_phone: '0912' + Math.floor(1000000 + Math.random() * 9000000),
      order_type: Math.random() > 0.5 ? 'indoor' : 'outdoor',
      address: 'تهران، ولیعصر، کوچه دوم',
      plaque: '۴',
      floor: '۲',
      unit: '۸',
      description: 'لطفاً گرم ارسال شود.',
      total_amount: randomProduct.price,
      status: 'registered',
      created_jalali: liveTime.split(' - ')[0] + ' ' + liveTime.split(' - ')[1],
      items: [{
        product_name: randomProduct.name,
        quantity: 1,
        price: randomProduct.price
      }]
    };

    setOrders([simOrder, ...orders]);
    playNotificationSound();
    showNotification(`سفارش جدید دریافت شد: ${code}`, 'success');
  };

  // تغییر وضعیت سفارش در ادمین
  const handleUpdateOrderStatus = (code: string, newStatus: string) => {
    setOrders(orders.map(o => o.order_code === code ? { ...o, status: newStatus } : o));
    showNotification(`وضعیت سفارش ${code} بروزرسانی شد.`, 'info');
  };

  // مدیریت افزودن/ویرایش محصول
  const [editingProduct, setEditingProduct] = useState<any | null>(null);
  const [prodFormName, setProdFormName] = useState('');
  const [prodFormCategory, setProdFormCategory] = useState(1);
  const [prodFormPrice, setProdFormPrice] = useState(0);
  const [prodFormDiscount, setProdFormDiscount] = useState(0);
  const [prodFormDesc, setProdFormDesc] = useState('');
  const [prodFormIngredients, setProdFormIngredients] = useState('');
  const [prodFormImage, setProdFormImage] = useState('');
  const [prodFormAvailable, setProdFormAvailable] = useState(true);
  const [prodFormPopular, setProdFormPopular] = useState(false);
  const [prodFormNew, setProdFormNew] = useState(false);
  const [prodFormVisible, setProdFormVisible] = useState(true);

  // لودر تصویر لوکال و تبدیل به Base64
  const handleImageUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    
    if (file.size > 2 * 1024 * 1024) { // محدودیت حجم ۲ مگابایت
      showNotification('حجم تصویر نباید بیشتر از ۲ مگابایت باشد.', 'error');
      return;
    }
    
    const reader = new FileReader();
    reader.onload = (event) => {
      if (event.target?.result) {
        setProdFormImage(event.target.result as string);
        showNotification('تصویر با موفقیت آپلود و متصل شد.', 'success');
      }
    };
    reader.readAsDataURL(file);
  };

  const handleProductSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingProduct) {
      // ویرایش
      setProducts(products.map(p => p.id === editingProduct.id ? {
        ...p,
        name: prodFormName,
        category_id: Number(prodFormCategory),
        price: Number(prodFormPrice),
        discount: Number(prodFormDiscount),
        description: prodFormDesc,
        ingredients: prodFormIngredients,
        image: prodFormImage || p.image,
        is_available: prodFormAvailable,
        is_popular: prodFormPopular,
        is_new: prodFormNew,
        is_visible: prodFormVisible
      } : p));
      showNotification('محصول با موفقیت ویرایش شد.', 'success');
    } else {
      // افزودن جدید
      const newProd = {
        id: products.length + 1,
        name: prodFormName,
        category_id: Number(prodFormCategory),
        price: Number(prodFormPrice),
        discount: Number(prodFormDiscount),
        description: prodFormDesc,
        ingredients: prodFormIngredients,
        image: prodFormImage || 'https://images.unsplash.com/photo-1510972527409-cef5e0af073c?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3',
        is_available: prodFormAvailable,
        is_popular: prodFormPopular,
        is_new: prodFormNew,
        is_visible: prodFormVisible,
        sort_order: products.length + 1
      };
      setProducts([...products, newProd]);
      showNotification('محصول جدید اضافه شد.', 'success');
    }
    setEditingProduct(null);
    resetProductForm();
  };

  const resetProductForm = () => {
    setProdFormName('');
    setProdFormCategory(categories[0]?.id || 1);
    setProdFormPrice(0);
    setProdFormDiscount(0);
    setProdFormDesc('');
    setProdFormIngredients('');
    setProdFormImage('');
    setProdFormAvailable(true);
    setProdFormPopular(false);
    setProdFormNew(false);
    setProdFormVisible(true);
  };

  const handleStartEditProduct = (p: any) => {
    setEditingProduct(p);
    setProdFormName(p.name);
    setProdFormCategory(p.category_id);
    setProdFormPrice(p.price);
    setProdFormDiscount(p.discount);
    setProdFormDesc(p.description);
    setProdFormIngredients(p.ingredients || '');
    setProdFormImage(p.image);
    setProdFormAvailable(p.is_available);
    setProdFormPopular(p.is_popular);
    setProdFormNew(p.is_new);
    setProdFormVisible(p.is_visible);
  };

  const handleDeleteProduct = (id: number) => {
    if (confirm('آیا از حذف این محصول اطمینان دارید؟')) {
      setProducts(products.filter(p => p.id !== id));
      showNotification('محصول حذف شد.', 'info');
    }
  };

  // مدیریت دسته‌بندی‌ها در ادمین
  const [editingCategory, setEditingCategory] = useState<any | null>(null);
  const [catFormName, setCatFormName] = useState('');
  const [catFormIcon, setCatFormIcon] = useState('coffee');
  const [catFormOrder, setCatFormOrder] = useState(0);

  const handleCategorySubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (editingCategory) {
      setCategories(categories.map(c => c.id === editingCategory.id ? {
        ...c,
        name: catFormName,
        icon: catFormIcon,
        sort_order: Number(catFormOrder)
      } : c));
      showNotification('دسته‌بندی با موفقیت ویرایش شد.', 'success');
    } else {
      const newCat = {
        id: categories.length + 1,
        name: catFormName,
        icon: catFormIcon,
        sort_order: Number(catFormOrder) || categories.length + 1
      };
      setCategories([...categories, newCat]);
      showNotification('دسته‌بندی جدید ایجاد شد.', 'success');
    }
    setEditingCategory(null);
    setCatFormName('');
    setCatFormIcon('coffee');
    setCatFormOrder(0);
  };

  const handleDeleteCategory = (id: number) => {
    if (confirm('با حذف دسته، تمامی محصولات مرتبط نیز حذف خواهند شد. مطمئن هستید؟')) {
      setCategories(categories.filter(c => c.id !== id));
      setProducts(products.filter(p => p.category_id !== id));
      showNotification('دسته‌بندی حذف شد.', 'info');
    }
  };

  // چاپ فیش فیزیکی در ادمین
  const handlePrintInvoice = (order: any) => {
    const w = window.open('', '_blank');
    if (!w) return;
    w.document.write(`
      <html dir="rtl" fa>
      <head>
          <title>فاکتور خرید ${order.order_code}</title>
          <style>
              body { font-family: 'Tahoma', sans-serif; padding: 20px; font-size: 12px; line-height: 20px; color: #333; }
              .invoice-box { max-width: 350px; margin: auto; padding: 10px; border: 1px dashed #bbb; }
              .text-center { text-align: center; }
              .flex-between { display: flex; justify-content: space-between; }
              .mt-10 { margin-top: 10px; }
              .border-top { border-top: 1px dashed #ddd; padding-top: 8px; margin-top: 8px; }
          </style>
      </head>
      <body onload="window.print()">
          <div class="invoice-box">
              <h2 class="text-center" style="margin-bottom: 5px;">فیش رسمی صندوق کافه</h2>
              <p class="text-center" style="margin-top: 0; font-size: 10px;">کد سفارش: ${order.order_code}</p>
              <div class="border-top">
                  <div class="flex-between"><span>مشتری:</span> <span>${order.customer_name}</span></div>
                  <div class="flex-between"><span>تلفن:</span> <span>${order.customer_phone}</span></div>
                  <div class="flex-between"><span>نوع تحویل:</span> <span>${order.order_type === 'indoor' ? 'حضوری' : 'غیرحضوری'}</span></div>
                  <div class="flex-between"><span>زمان:</span> <span>${order.created_jalali}</span></div>
              </div>
              <div class="border-top">
                  <strong style="display: block; margin-bottom: 5px;">آیتم‌های خرید:</strong>
                  ${order.items.map((i: any) => `
                      <div class="flex-between">
                          <span>${i.product_name} × ${i.quantity}</span>
                          <span>${i.price.toLocaleString()} تومان</span>
                      </div>
                  `).join('')}
              </div>
              <div class="border-top" style="font-weight: bold; font-size: 14px;">
                  <div class="flex-between"><span>مجموع فاکتور:</span> <span>${order.total_amount.toLocaleString()} تومان</span></div>
              </div>
              <div class="border-top text-center" style="font-size: 10px; color: #777;">
                  ساعت خوبی را برای شما در ${settings.cafe_name} آرزومندیم. <br> با سپاس از حسن انتخاب شما!
              </div>
          </div>
      </body>
      </html>
    `);
    w.document.close();
  };

  // دانلود و چاپ گزارش کامل فروش به صورت PDF فارسی شیک و بهینه
  const handleExportPDF = () => {
    const totalOrders = orders.length;
    const completedOrders = orders.filter(o => o.status === 'completed').length;
    const cancelledOrders = orders.filter(o => o.status === 'cancelled').length;
    const totalSales = orders.filter(o => o.status !== 'cancelled').reduce((acc, o) => acc + o.total_amount, 0);
    const avgOrderValue = totalOrders > 0 ? Math.round(totalSales / totalOrders) : 0;

    const w = window.open('', '_blank');
    if (!w) return;
    w.document.write(`
      <html dir="rtl" lang="fa">
      <head>
          <title>گزارش کامل فروش و عملکرد کافه</title>
          <style>
              @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;700;900&display=swap');
              body { 
                  font-family: 'Vazirmatn', 'Tahoma', sans-serif; 
                  padding: 40px; 
                  font-size: 11px; 
                  line-height: 22px; 
                  color: #1f2937; 
                  background: #fff;
              }
              .header { 
                  display: flex; 
                  justify-content: space-between; 
                  align-items: center; 
                  border-bottom: 2px solid #e5e7eb; 
                  padding-bottom: 20px; 
                  margin-bottom: 25px; 
              }
              .header h1 { margin: 0; font-size: 20px; font-weight: 900; color: #8b5a2b; }
              .header p { margin: 5px 0 0 0; color: #6b7280; font-size: 11px; }
              .stats-grid { 
                  display: grid; 
                  grid-template-cols: repeat(4, 1fr); 
                  gap: 15px; 
                  margin-bottom: 30px; 
              }
              .stat-card { 
                  background: #f9fafb; 
                  border: 1px solid #f3f4f6; 
                  padding: 15px; 
                  border-radius: 12px; 
                  text-align: center; 
              }
              .stat-card h3 { margin: 0; color: #6b7280; font-size: 10px; font-weight: bold; }
              .stat-card p { margin: 8px 0 0 0; font-size: 15px; font-weight: 900; color: #111827; }
              table { 
                  width: 100%; 
                  border-collapse: collapse; 
                  margin-top: 15px; 
              }
              th, td { 
                  padding: 10px 12px; 
                  text-align: right; 
                  border-bottom: 1px solid #f3f4f6; 
              }
              th { 
                  background: #f9fafb; 
                  color: #374151; 
                  font-weight: 700; 
              }
              tr:hover { background: #f9fafb; }
              .badge { 
                  padding: 3px 8px; 
                  border-radius: 50px; 
                  font-size: 9px; 
                  font-weight: bold; 
              }
              .badge-registered { background: #dbeafe; color: #1e40af; }
              .badge-preparing { background: #fef3c7; color: #92400e; }
              .badge-completed { background: #d1fae5; color: #065f46; }
              .badge-cancelled { background: #fee2e2; color: #991b1b; }
              .text-center { text-align: center; }
              .footer { 
                  margin-top: 50px; 
                  border-top: 1px solid #e5e7eb; 
                  padding-top: 15px; 
                  text-align: center; 
                  color: #9ca3af; 
                  font-size: 9px; 
              }
              @media print {
                  body { padding: 10px; }
                  button { display: none; }
              }
          </style>
      </head>
      <body onload="window.print()">
          <div class="header">
              <div>
                  <h1>گزارش فروش و عملکرد مدیریتی</h1>
                  <p>تهیه شده به صورت رسمی برای کافه: ${settings.cafe_name}</p>
              </div>
              <div style="text-align: left;">
                  <span style="color: #6b7280;">تاریخ گزارش: 1405/07/06</span><br>
                  <span style="color: #6b7280;">تلفن پشتیبانی: ${settings.cafe_phone}</span>
              </div>
          </div>

          <div class="stats-grid">
              <div class="stat-card">
                  <h3>کل فاکتورهای ثبت‌شده</h3>
                  <p>${toPersianDigits(totalOrders)} عدد</p>
              </div>
              <div class="stat-card">
                  <h3>مجموع درآمد کل</h3>
                  <p>${toPersianDigits(totalSales.toLocaleString())} تومان</p>
              </div>
              <div class="stat-card">
                  <h3>میانگین ارزش فاکتور</h3>
                  <p>${toPersianDigits(avgOrderValue.toLocaleString())} تومان</p>
              </div>
              <div class="stat-card">
                  <h3>نرخ تکمیل سفارش</h3>
                  <p>${toPersianDigits(totalOrders > 0 ? Math.round((completedOrders / totalOrders) * 100) : 0)}٪</p>
              </div>
          </div>

          <h2 style="font-size: 13px; font-weight: bold; color: #374151; margin-bottom: 10px;">آرشیو ریز فاکتورها و تراکنش‌ها</h2>
          <table>
              <thead>
                  <tr>
                      <th>کد سفارش</th>
                      <th>مشتری</th>
                      <th>شماره همراه</th>
                      <th>نوع تحویل</th>
                      <th>مبلغ فاکتور</th>
                      <th class="text-center">وضعیت فاکتور</th>
                      <th>زمان سفارش</th>
                  </tr>
              </thead>
              <tbody>
                  ${orders.map((o: any) => `
                      <tr>
                          <td style="font-weight: bold; font-family: monospace;">${o.order_code}</td>
                          <td>${o.customer_name}</td>
                          <td>${toPersianDigits(o.customer_phone)}</td>
                          <td>${o.order_type === 'indoor' ? 'حضوری' : 'غیرحضوری (ارسال)'}</td>
                          <td style="font-weight: bold;">${toPersianDigits(o.total_amount.toLocaleString())} تومان</td>
                          <td class="text-center">
                              <span class="badge badge-${o.status}">
                                  ${o.status === 'registered' ? 'ثبت شده' : 
                                    o.status === 'preparing' ? 'در حال آماده‌سازی' : 
                                    o.status === 'completed' ? 'تکمیل شده' : 'لغو شده'}
                              </span>
                          </td>
                          <td>${toPersianDigits(o.created_jalali)}</td>
                      </tr>
                  `).join('')}
              </tbody>
          </table>

          <div class="footer">
              این گزارش به صورت خودکار و به سفارش مدیریت توسط سامانه مدیریت یکپارچه ${settings.cafe_name} صادر گردیده است.
          </div>
      </body>
      </html>
    `);
    w.document.close();
    showNotification('گزارش PDF با موفقیت صادر شد و آماده پرینت است.', 'success');
  };

  // -------------------------------------------------------------------
  // ۳. مدیریت بخش کدهای سی‌پنل (cPanel Packages & Code Viewer)
  // -------------------------------------------------------------------
  const [selectedFile, setSelectedFile] = useState<string>('index.php');
  const [copiedFile, setCopiedFile] = useState(false);

  const handleCopyCode = (code: string) => {
    navigator.clipboard.writeText(code);
    setCopiedFile(true);
    setTimeout(() => setCopiedFile(false), 2000);
    showNotification('کد کپی شد.', 'success');
  };

  // دانلود پکیج آماده سی‌پنل به صورت فایل فشرده ZIP مستقیم در مروگر با jszip!
  const [isZipping, setIsZipping] = useState(false);
  const handleDownloadCPanelPackage = async () => {
    setIsZipping(true);
    try {
      const zip = new JSZip();
      
      // اضافه کردن فایل‌های کلاینت به فایل زیپ
      Object.entries(phpFiles).forEach(([filePath, content]) => {
        zip.file(filePath, content);
      });

      // ایجاد فولدر تصاویر آپلودی خالی
      zip.folder("uploads");

      const blob = await zip.generateAsync({ type: 'blob' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.setAttribute("href", url);
      link.setAttribute("download", `${settings.cafe_name.replace(/\s+/g, '_')}_cpanel_package.zip`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      showNotification('پکیج زیپ با موفقیت آماده و دانلود شد!', 'success');
    } catch (e) {
      showNotification('خطایی در فشرده‌سازی پکیج رخ داد.', 'error');
    } finally {
      setIsZipping(false);
    }
  };

  // -------------------------------------------------------------------
  // ابزار مدیریت اعلانات کاستوم کلاینت
  // -------------------------------------------------------------------
  const [notification, setNotification] = useState<{ text: string, type: 'success' | 'error' | 'info' } | null>(null);
  const showNotification = (text: string, type: 'success' | 'error' | 'info' = 'info') => {
    setNotification({ text, type });
  };
  useEffect(() => {
    if (notification) {
      const timer = setTimeout(() => {
        setNotification(null);
      }, 3000);
      return () => clearTimeout(timer);
    }
  }, [notification]);

  return (
    <div dir="rtl" className="flex flex-col min-h-screen bg-[#FAF6F0] dark:bg-[#0f0e0c] text-stone-900 dark:text-white/95 transition-colors duration-300 select-none">
      
      {/* =================================================================== */}
      {/* نمای ۱: منوی دیجیتال مشتری */}
      {/* =================================================================== */}
      {appMode === 'customer' && (
        <div className="flex-1 flex flex-col animate-slide-up">
          {/* بنر کافه */}
          <div className="relative w-full h-48 md:h-64 bg-stone-900/40 overflow-hidden">
            <div className="absolute inset-0 bg-gradient-to-r from-stone-900/30 via-transparent to-stone-900/30 flex items-center justify-center">
              <div className="absolute inset-0 bg-[radial-gradient(#c49b63_1px,transparent_1px)] [background-size:16px_16px] opacity-10"></div>
              <Coffee className="w-24 h-24 text-[#c49b63]/10" />
            </div>
            <div className="absolute inset-0 bg-gradient-to-t from-[#FAF6F0] dark:from-[#0f0e0c] via-transparent to-transparent"></div>
          </div>

          <div className="max-w-6xl md:max-w-4xl lg:max-w-4xl mx-auto px-4 -mt-20 relative z-10 pb-12 w-full">
            {/* کارت معرفی کافه */}
            <div className="glass rounded-3xl p-6 shadow-2xl transition-all duration-300">
              <div className="flex flex-col sm:flex-row items-center gap-6">
                <div className="w-24 h-24 rounded-full bg-[#c49b63]/10 flex items-center justify-center text-[#c49b63] border border-[#c49b63]/30 shadow-lg shrink-0 overflow-hidden">
                  {settings.logo_url ? (
                    <img src={settings.logo_url} alt="لوگو کافه" className="w-full h-full object-cover" />
                  ) : (
                    <Coffee className="w-12 h-12" />
                  )}
                </div>
                <div className="text-center sm:text-right flex-1 w-full">
                  <div className="flex flex-col sm:flex-row items-center justify-between gap-3 w-full">
                    <div className="flex items-center justify-center sm:justify-start gap-2 w-full sm:w-auto">
                      <h2 className="text-2xl font-black text-white">{settings.cafe_name}</h2>
                      <span className="bg-[#c49b63]/20 text-[#c49b63] text-[10px] font-bold px-2.5 py-1 rounded-md border border-[#c49b63]/30">منوی رسمی</span>
                    </div>
                  </div>
                  <p className="text-sm text-white/70 mt-2 line-clamp-2 leading-relaxed">{settings.cafe_description}</p>
                  <div className="flex flex-wrap justify-center sm:justify-start gap-4 mt-4 text-xs text-stone-500 dark:text-white/60">
                    <span className="flex items-center gap-1.5 bg-stone-500/5 dark:bg-white/5 border border-stone-200 dark:border-white/10 px-3 py-1.5 rounded-full">
                      <Coffee className="w-4 h-4 text-[#c49b63]" />
                      <span>{toPersianDigits(settings.working_hours)}</span>
                    </span>
                    <span className="flex items-center gap-1.5 bg-stone-500/5 dark:bg-white/5 border border-stone-200 dark:border-white/10 px-3 py-1.5 rounded-full">
                      <Coffee className="w-4 h-4 text-[#c49b63]" />
                      <span className="dir-ltr">{toPersianDigits(settings.cafe_phone)}</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            {/* بخش جستجو و فیلترها */}
            <div className="mt-8 flex flex-col md:flex-row gap-4 items-center justify-between">
              {/* سرچ بار آنی */}
              <div className="relative w-full md:max-w-md">
                <span className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-white/40">
                  <Search className="w-5 h-5" />
                </span>
                <input 
                  type="text" 
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="جستجوی نوشیدنی، کیک یا دسر..." 
                  className="w-full pl-4 pr-10 py-3 rounded-2xl bg-white/5 border border-white/10 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#c49b63]/20 focus:border-[#c49b63] transition-all shadow-md text-sm"
                />
              </div>

              {/* فیلترهای پیشرفته */}
              <div className="flex flex-wrap gap-2 w-full md:w-auto justify-end">
                {[
                  { id: 'default', label: 'همه' },
                  { id: 'new', label: 'جدیدترین‌ها' },
                  { id: 'popular', label: 'محبوب‌ترین‌ها' },
                  { id: 'discount', label: 'دارای تخفیف' }
                ].map(item => (
                  <button 
                    key={item.id}
                    onClick={() => setSortType(item.id as any)}
                    className={`px-4 py-2 rounded-xl text-xs font-semibold transition-all shadow-md ${sortType === item.id ? 'bg-[#c49b63] text-black accent-glow border border-[#c49b63] font-bold' : 'bg-white/5 text-white/75 border border-white/10 hover:bg-white/10'}`}
                  >
                    {item.label}
                  </button>
                ))}
              </div>
            </div>

            {/* دسته‌بندی‌ها */}
            <div className="mt-8">
              <h3 className="text-xs font-bold text-white/40 mb-3 uppercase tracking-wider">دسته‌بندی‌ها</h3>
              <div className="flex gap-3 overflow-x-auto pb-3 scrollbar-none">
                <button 
                  onClick={() => setSelectedCategory(0)}
                  className={`shrink-0 px-5 py-3 rounded-2xl flex items-center gap-2.5 font-bold text-sm shadow-md transition-all duration-300 ${selectedCategory === 0 ? 'bg-[#c49b63] text-black accent-glow border border-[#c49b63]' : 'bg-white/5 text-white/80 border border-white/10 hover:bg-white/10'}`}
                >
                  <i className="fa-solid fa-list text-inherit" />
                  <span>همه دسته‌ها</span>
                </button>

                {categories.map((cat) => (
                  <button 
                    key={cat.id}
                    onClick={() => setSelectedCategory(cat.id)}
                    className={`shrink-0 px-5 py-3 rounded-2xl flex items-center gap-2.5 font-bold text-sm shadow-md transition-all duration-300 ${selectedCategory === cat.id ? 'bg-[#c49b63] text-black accent-glow border border-[#c49b63]' : 'bg-white/5 text-white/80 border border-white/10 hover:bg-white/10'}`}
                  >
                    {renderCategoryIcon(cat.icon, "text-inherit text-sm")}
                    <span>{cat.name}</span>
                  </button>
                ))}
              </div>
            </div>

            {/* لیست محصولات منو - گرید با تراکم بسیار بالا و لوکس جهت نمایش آیتم‌های بیشتر */}
            <div className="mt-8 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4">
              {filteredProducts.map((prod) => {
                const finalPrice = prod.price - (prod.price * prod.discount / 100);
                return (
                  <div key={prod.id} className="glass p-2.5 rounded-2xl flex flex-col group transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:border-white/20">
                    <div className="relative w-full h-28 md:h-36 rounded-xl overflow-hidden cursor-pointer mb-2.5" onClick={() => setSelectedProductDetail(prod)}>
                      <img src={prod.image} alt={prod.name} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                      
                      {/* بچ‌ها با سایز فشرده */}
                      <div className="absolute top-2 right-2 flex flex-col gap-1 z-10">
                        {prod.is_popular && (
                          <span className="bg-[#c49b63] text-black text-[8px] font-black px-2 py-0.5 rounded-full shadow-md flex items-center gap-0.5 accent-glow">
                            <Star className="w-2 h-2 fill-black text-black" />
                            محبوب
                          </span>
                        )}
                        {prod.is_new && <span className="bg-teal-500 text-white text-[8px] font-black px-2 py-0.5 rounded-full shadow-md">جدید</span>}
                        {prod.discount > 0 && <span className="bg-red-500 text-white text-[8px] font-black px-2 py-0.5 rounded-full shadow-md">٪{toPersianDigits(prod.discount)} تخفیف</span>}
                      </div>

                      {!prod.is_available && (
                        <div className="absolute inset-0 bg-black/80 backdrop-blur-xs flex items-center justify-center">
                          <span className="bg-white/10 backdrop-blur-md text-white px-2.5 py-1 rounded-xl text-[10px] font-black shadow-md border border-white/20">ناموجود</span>
                        </div>
                      )}
                    </div>

                    <div className="flex-1 flex flex-col">
                      <h4 className="text-xs md:text-sm font-bold text-white group-hover:text-[#c49b63] transition-colors cursor-pointer line-clamp-1" onClick={() => setSelectedProductDetail(prod)}>
                        {prod.name}
                      </h4>
                      <p className="text-[10px] md:text-xs text-white/50 mt-1 line-clamp-2 leading-relaxed flex-1">
                        {prod.description}
                      </p>

                      <div className="mt-3 pt-2.5 border-t border-white/10 flex items-center justify-between">
                        <div className="flex flex-col">
                          {prod.discount > 0 ? (
                            <>
                              <span className="text-[10px] text-white/40 line-through decoration-red-500/50">{toPersianDigits(prod.price.toLocaleString())}</span>
                              <span className="text-xs md:text-sm font-black text-white">{toPersianDigits(finalPrice.toLocaleString())} <span className="text-[9px] font-normal text-white/50">تومان</span></span>
                            </>
                          ) : (
                            <span className="text-xs md:text-sm font-black text-[#c49b63]">{toPersianDigits(prod.price.toLocaleString())} <span className="text-[9px] font-normal text-white/50">تومان</span></span>
                          )}
                        </div>

                        {prod.is_available ? (
                          <button onClick={() => addToCart(prod)} className="w-7 h-7 md:w-8 md:h-8 rounded-full bg-white/10 hover:bg-[#c49b63] hover:text-black text-white flex items-center justify-center hover:scale-105 transition-all shadow-md active:scale-95">
                            <Plus className="w-3.5 h-3.5" />
                          </button>
                        ) : (
                          <button disabled className="w-7 h-7 md:w-8 md:h-8 rounded-full bg-white/5 text-white/20 flex items-center justify-center cursor-not-allowed">
                            <Minus className="w-3.5 h-3.5" />
                          </button>
                        )}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {/* فلوتینگ سبد خرید به روت کامپوننت انتقال یافت تا از مشکل تداخل با افکت انیمیشن ترنسفورم والد جلوگیری شود */}
        </div>
      )}

      {/* =================================================================== */}
      {/* نمای ۲: پنل مدیریت ادمین */}
      {/* =================================================================== */}
      {appMode === 'admin' && (
        !isAdminLoggedIn ? (
          <div className="flex-1 flex items-center justify-center p-6 min-h-[80vh] relative">
            <div className="glass rounded-3xl p-8 max-w-sm w-full space-y-6 shadow-2xl relative border border-white/10 overflow-hidden text-center">
              <div className="absolute inset-0 bg-[radial-gradient(#c49b63_1px,transparent_1px)] [background-size:16px_16px] opacity-10 pointer-events-none"></div>
              
              <div className="w-16 h-16 mx-auto rounded-full bg-[#c49b63]/10 flex items-center justify-center text-[#c49b63] border border-[#c49b63]/30 shadow-lg">
                <Lock className="w-7 h-7" />
              </div>
              
              <div>
                <h2 className="text-xl font-black text-white">ورود به پنل مدیریت</h2>
                <p className="text-xs text-white/50 mt-1.5">جهت دسترسی به سفارشات و مدیریت کافه، اطلاعات مدیریت را وارد کنید</p>
              </div>

              <form onSubmit={handleAdminLogin} className="space-y-4 text-xs font-semibold text-right">
                <div>
                  <label className="block text-white/40 mb-1.5">نام کاربری ارشد *</label>
                  <input 
                    type="text" 
                    required
                    value={adminUser}
                    onChange={e => setAdminUser(e.target.value)}
                    placeholder="نام کاربری مدیریت..." 
                    className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white font-mono text-center focus:outline-none focus:border-[#c49b63] focus:ring-1 focus:ring-[#c49b63]/30" 
                  />
                </div>
                <div>
                  <label className="block text-white/40 mb-1.5">رمز عبور امنیتی *</label>
                  <input 
                    type="password" 
                    required 
                    value={adminPass} 
                    onChange={e => setAdminPass(e.target.value)} 
                    placeholder="رمز عبور خود را وارد کنید..."
                    className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white font-mono text-center focus:outline-none focus:border-[#c49b63] focus:ring-1 focus:ring-[#c49b63]/30" 
                  />
                </div>

                <button 
                  type="submit" 
                  className="w-full bg-[#c49b63] hover:bg-[#c49b63]/90 text-black py-3 rounded-xl font-bold transition-all shadow-md cursor-pointer accent-glow active:scale-98"
                >
                  تأیید و ورود امن
                </button>
              </form>
            </div>
          </div>
        ) : (
          <div className="flex-1 flex flex-col bg-[#0f0e0c] animate-slide-up relative">
            {/* پاپ‌آپ‌های اعلانات زنده سفارشات جدید برای ادمین */}
            {adminAlerts.length > 0 && (
              <div className="fixed top-18 right-4 left-4 md:left-auto md:w-80 z-[100] space-y-2.5">
                {adminAlerts.map(alert => (
                  <div key={alert.id} className="bg-[#b5651d] text-white p-4 rounded-2xl shadow-2xl border border-white/10 flex items-center justify-between gap-3 animate-slide-up">
                    <div className="flex items-center gap-2">
                      <div className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                        <AlertTriangle className="w-4 h-4 text-white animate-bounce" />
                      </div>
                      <div>
                        <p className="text-xs font-black">{alert.message}</p>
                        <p className="text-[9px] font-mono opacity-80 mt-0.5">کد سفارش: {alert.code}</p>
                      </div>
                    </div>
                    <button 
                      onClick={() => setAdminAlerts(prev => prev.filter(a => a.id !== alert.id))}
                      className="p-1 rounded-full hover:bg-white/20 transition-colors text-white shrink-0 cursor-pointer"
                    >
                      <Plus className="w-4 h-4 rotate-45" />
                    </button>
                  </div>
                ))}
              </div>
            )}

            {/* هدر یکپارچه جدید پنل مدیریت ادمین */}
            <header className="sticky top-0 z-40 bg-[#131210]/95 backdrop-blur-md border-b border-white/10 px-4 py-3 flex flex-wrap items-center justify-between gap-4 shadow-lg">
              {/* نام کافه و عنوان پنل مدیریت */}
              <div className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-full bg-[#c49b63]/10 flex items-center justify-center text-[#c49b63] border border-[#c49b63]/25">
                  <Coffee className="w-5 h-5 animate-pulse" />
                </div>
                <div className="hidden sm:block">
                  <h1 className="text-sm font-black text-white">{settings.cafe_name}</h1>
                  <p className="text-[10px] text-white/40 font-bold">پنل مدیریت یکپارچه</p>
                </div>
              </div>

              {/* دکمه‌های ناوبری تب‌های ادمین با آیکون‌های زیبا و توولتیپ */}
              <div className="flex items-center gap-1.5 bg-white/5 border border-white/10 p-1 rounded-2xl overflow-x-auto scrollbar-none max-w-full">
                {[
                  { id: 'dashboard', label: 'مانیتورینگ و آمار', icon: LayoutDashboard },
                  { id: 'products', label: 'مدیریت محصولات', icon: Coffee },
                  { id: 'categories', label: 'مدیریت دسته‌ها', icon: Layers },
                  { id: 'archive', label: 'آرشیو سفارشات', icon: ShoppingBag },
                  { id: 'reports', label: 'گزارشات و خروجی', icon: BarChart3 },
                  { id: 'settings', label: 'تنظیمات سیستم', icon: SettingsIcon }
                ].map(item => {
                  const IconComp = item.icon;
                  const isActive = adminTab === item.id;
                  return (
                    <button
                      key={item.id}
                      onClick={() => setAdminTab(item.id as any)}
                      title={item.label}
                      className={`relative p-2.5 rounded-xl transition-all cursor-pointer group shrink-0 ${isActive ? 'bg-[#c49b63] text-black shadow-md font-black accent-glow scale-105' : 'text-stone-300 hover:bg-white/5 hover:text-white'}`}
                    >
                      <IconComp className="w-4.5 h-4.5 text-inherit" />
                      {/* تولتیپ اختصاصی فارسی متحرک */}
                      <span className="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-black text-white text-[9px] font-bold px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap border border-white/10 shadow-lg z-[100]">
                        {item.label}
                      </span>
                    </button>
                  );
                })}
              </div>

              {/* بخش دکمه‌های آماده دانلود و پیش‌نمایش در هدر ادمین */}
              <div className="flex items-center gap-1.5 shrink-0">
                {/* دکمه‌های سوییچ و دمو */}
                <button 
                  onClick={() => { setAppMode('customer'); window.location.hash = ''; }}
                  title="📱 مشاهده منوی مشتری"
                  className="p-2.5 rounded-xl bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-all cursor-pointer group relative"
                >
                  <ExternalLink className="w-4.5 h-4.5 text-inherit" />
                  <span className="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-black text-white text-[9px] font-bold px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap border border-white/10 shadow-lg z-[100]">
                    مشاهده منوی مشتری
                  </span>
                </button>
                <button 
                  onClick={() => { setAppMode('code'); window.location.hash = '#code'; }}
                  title="📦 دانلود کدهای cPanel"
                  className="p-2.5 rounded-xl bg-emerald-600/20 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-600/30 transition-all cursor-pointer group relative"
                >
                  <Download className="w-4.5 h-4.5 text-inherit" />
                  <span className="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-black text-white text-[9px] font-bold px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap border border-white/10 shadow-lg z-[100]">
                    دانلود کدهای cPanel
                  </span>
                </button>
                
                {/* دکمه خروج از ادمین */}
                <button 
                  onClick={() => { setIsAdminLoggedIn(false); setAdminPass(''); }}
                  title="خروج از پنل"
                  className="p-2.5 rounded-xl bg-red-600/20 border border-red-500/30 text-red-400 hover:bg-red-600/30 transition-all cursor-pointer group relative"
                >
                  <LogOut className="w-4.5 h-4.5 text-inherit" />
                  <span className="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 bg-black text-white text-[9px] font-bold px-2 py-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap border border-white/10 shadow-lg z-[100]">
                    خروج از پنل
                  </span>
                </button>
              </div>
            </header>

            {/* محتوای تب انتخاب شده ادمین */}
            <main className="flex-1 p-6 md:p-8 overflow-y-auto bg-[#0f0e0c]">
              <div className="max-w-4xl lg:max-w-4xl xl:max-w-4xl mx-auto w-full space-y-6">
            
            {/* ۱. تب داشبورد و مانیتور زنده */}
            {adminTab === 'dashboard' && (
              <div className="space-y-6">
                <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                  <div>
                    <h2 className="text-xl font-black text-white">میز کار مانیتورینگ زنده</h2>
                    <p className="text-xs text-white/50 mt-1">سفارشات جدید ثبت شده مشتری به محض دریافت در اینجا نمایش داده خواهند شد</p>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="bg-white/5 border border-white/10 px-4 py-2 rounded-xl text-center shadow-sm shrink-0">
                      <span className="text-[10px] font-bold text-white/40 block">ساعت رسمی ایران</span>
                      <span className="text-xs font-black text-[#c49b63] tracking-wider">{liveTime}</span>
                    </div>
                  </div>
                </div>

                {/* آمار بالای صفحه */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                  <div className="glass p-4 rounded-2xl shadow-sm">
                    <span className="text-[10px] text-white/40 font-semibold">سفارشات امروز</span>
                    <span className="text-lg font-black text-white block mt-1">{toPersianDigits(orders.length)}</span>
                  </div>
                  <div className="glass p-4 rounded-2xl shadow-sm">
                    <span className="text-[10px] text-white/40 font-semibold">کل فروش موثر</span>
                    <span className="text-lg font-black text-[#c49b63] block mt-1">{toPersianDigits(salesToday.toLocaleString())} <span className="text-xs font-normal">تومان</span></span>
                  </div>
                  <div className="glass p-4 rounded-2xl shadow-sm">
                    <span className="text-[10px] text-white/40 font-semibold">سفارشات در صف</span>
                    <span className="text-lg font-black text-amber-500 block mt-1">{toPersianDigits(activeOrders.length)}</span>
                  </div>
                  <div className="glass p-4 rounded-2xl shadow-sm">
                    <span className="text-[10px] text-white/40 font-semibold">محصولات فعال</span>
                    <span className="text-lg font-black text-white block mt-1">{toPersianDigits(products.length)}</span>
                  </div>
                </div>

                {/* بنر هوشمند دریافت کدهای نهایی سی‌پنل هماهنگ با تم جدید سایت */}
                <div className="glass p-5 md:p-6 rounded-3xl border border-[#c49b63]/20 relative overflow-hidden shadow-xl">
                  <div className="absolute inset-0 bg-[radial-gradient(#c49b63_1px,transparent_1px)] [background-size:16px_16px] opacity-10 pointer-events-none"></div>
                  <div className="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                    <div className="text-center md:text-right space-y-1.5 flex-1">
                      <h3 className="text-sm md:text-base font-black text-white flex items-center justify-center md:justify-start gap-2">
                        <Download className="text-[#c49b63] w-5 h-5 animate-bounce shrink-0" />
                        <span>دریافت مستقیم پکیج کدهای نهایی سی‌پنل (cPanel)</span>
                      </h3>
                      <p className="text-[11px] md:text-xs text-white/60 leading-relaxed">
                        تمامی تغییرات اخیر، از جمله تم رنگی لوکس جدید، دکمه سبد خرید شناور و همگام‌سازی‌های اخیر به صورت خودکار در این پکیج قرار گرفته‌اند. فایل زیپ را دانلود کرده و بدون نیاز به هیچ تغییری روی هاست خود اکسترکت کنید.
                      </p>
                    </div>
                    <button 
                      onClick={handleDownloadCPanelPackage}
                      disabled={isZipping}
                      className="w-full md:w-auto bg-[#c49b63] hover:bg-[#b28b58] text-black font-black py-3 px-6 md:py-3.5 md:px-8 rounded-xl flex items-center justify-center gap-2 transition-all duration-300 hover:scale-105 active:scale-95 cursor-pointer accent-glow whitespace-nowrap shrink-0 shadow-lg shadow-[#c49b63]/15 text-xs md:text-sm"
                    >
                      <Download className="w-4 h-4 md:w-5 md:h-5 shrink-0" />
                      <span>{isZipping ? 'در حال زیپ کردن...' : 'دانلود پکیج ZIP پروژه'}</span>
                    </button>
                  </div>
                </div>

                {/* مانیتور سفارشات جدید */}
                <div className="glass p-5 rounded-2xl shadow-md">
                  <div className="flex items-center justify-between border-b border-white/10 pb-3 mb-4">
                    <h3 className="text-sm font-black text-white">فهرست سفارشات فعال کافه</h3>
                    <span className="bg-red-500/15 text-red-400 text-[10px] font-bold px-2.5 py-1 rounded-md border border-red-500/30">{toPersianDigits(activeOrders.length)} سفارش فعال</span>
                  </div>

                  <div className="space-y-4">
                    {activeOrders.length === 0 ? (
                      <div className="p-8 text-center text-white/40">هیچ سفارش فعالی در صف مانیتورینگ وجود ندارد. سفارش‌های تکمیل یا لغو شده به آرشیو منتقل شده‌اند.</div>
                    ) : (
                      activeOrders.map((order, idx) => (
                        <div key={idx} className="glass p-4 rounded-2xl border border-white/10 flex flex-col md:flex-row justify-between gap-4 transition-all hover:border-[#c49b63]/30">
                          <div className="space-y-1.5 flex-1 text-xs">
                            <div className="flex flex-wrap items-center gap-1.5">
                              <span className="font-black text-white">{order.order_code}</span>
                              <span className={`px-2 py-0.5 rounded-full text-[9px] font-extrabold ${
                                order.status === 'registered' ? 'bg-blue-500/15 text-blue-400 border border-blue-500/30' :
                                order.status === 'preparing' ? 'bg-yellow-500/15 text-yellow-400 border border-yellow-500/30' :
                                order.status === 'ready' ? 'bg-purple-500/15 text-purple-400 border border-purple-500/30' :
                                order.status === 'sent' ? 'bg-indigo-500/15 text-indigo-400 border border-indigo-500/30' :
                                order.status === 'completed' ? 'bg-green-500/15 text-green-400 border border-green-500/30' : 'bg-red-500/15 text-red-400 border border-red-500/30'
                              }`}>
                                {order.status === 'registered' ? 'ثبت شد' :
                                 order.status === 'preparing' ? 'آماده‌سازی' :
                                 order.status === 'ready' ? 'آماده تحویل' :
                                 order.status === 'sent' ? 'ارسال شد' :
                                 order.status === 'completed' ? 'تکمیل شد' : 'لغو شد'}
                              </span>
                              <span className="px-1.5 py-0.5 bg-white/10 text-white/70 text-[9px] font-bold rounded">
                                {order.order_type === 'indoor' ? 'حضوری' : 'غیرحضوری'}
                              </span>
                            </div>
                            <p className="font-semibold text-white/70">مشتری: <span className="font-bold text-white">{order.customer_name}</span> ({toPersianDigits(order.customer_phone)})</p>
                            {order.address && <p className="text-amber-500">آدرس: {order.address} پلاک {toPersianDigits(order.plaque)} طبقه {toPersianDigits(order.floor)} واحد {toPersianDigits(order.unit)}</p>}
                            {order.description && <p className="text-white/40">توضیح: {order.description}</p>}
                            
                            <div className="bg-black/30 p-2.5 rounded-xl border border-white/10 text-[11px] max-w-md text-white/80">
                              <ul className="space-y-1">
                                {order.items.map((it: any, i: number) => (
                                  <li key={i} className="flex justify-between">
                                    <span>{it.product_name} × {toPersianDigits(it.quantity)}</span>
                                    <span>{toPersianDigits((it.price * it.quantity).toLocaleString())} تومان</span>
                                  </li>
                                ))}
                              </ul>
                            </div>
                          </div>

                          <div className="flex flex-row md:flex-col items-end justify-between md:justify-center gap-3 border-t md:border-t-0 pt-3 md:pt-0 border-white/10">
                            <div className="text-right">
                              <span className="text-[10px] text-white/40 block">{toPersianDigits(order.created_jalali)}</span>
                              <span className="text-sm font-black text-[#c49b63] block mt-1">{toPersianDigits(order.total_amount.toLocaleString())} تومان</span>
                            </div>

                            <div className="flex items-center gap-1.5">
                              <select 
                                value={order.status}
                                onChange={e => handleUpdateOrderStatus(order.order_code, e.target.value)}
                                className="text-[10px] bg-black/40 border border-white/10 px-2 py-1 rounded-md font-bold text-white focus:border-[#c49b63]"
                              >
                                <option value="registered">ثبت شد</option>
                                <option value="preparing">آماده‌سازی</option>
                                <option value="ready">آماده تحویل</option>
                                <option value="sent">ارسال شد</option>
                                <option value="completed">تکمیل شد</option>
                                <option value="cancelled">لغو شد</option>
                              </select>

                              <button onClick={() => handlePrintInvoice(order)} className="bg-white/10 hover:bg-white/20 text-white p-2 rounded-lg transition-colors" title="چاپ فیش">
                                <Printer className="w-4 h-4" />
                              </button>
                            </div>
                          </div>
                        </div>
                      ))
                    )}
                  </div>
                </div>
              </div>
            )}

            {/* ۲. تب مدیریت محصولات ادمین */}
            {adminTab === 'products' && (
              <div className="space-y-6">
                <div className="flex flex-col md:flex-row gap-6">
                  {/* فرم افزودن ویرایش */}
                  <div className="w-full md:w-80 glass p-5 rounded-3xl shrink-0 h-fit space-y-4">
                    <h3 className="text-sm font-black text-white border-b border-white/10 pb-2 mb-2">
                      {editingProduct ? 'ویرایش اطلاعات محصول' : 'افزودن محصول جدید'}
                    </h3>
                    <form onSubmit={handleProductSubmit} className="space-y-4 text-xs font-semibold">
                      <div>
                        <label className="block text-white/40 mb-1">نام محصول *</label>
                        <input type="text" required value={prodFormName} onChange={e => setProdFormName(e.target.value)} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white" />
                      </div>
                      <div>
                        <label className="block text-white/40 mb-1">دسته‌ب بندی *</label>
                        <select value={prodFormCategory} onChange={e => setProdFormCategory(Number(e.target.value))} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white cursor-pointer">
                          {categories.map(c => <option key={c.id} value={c.id} className="bg-[#1a1917]">{c.name}</option>)}
                        </select>
                      </div>
                      <div className="grid grid-cols-2 gap-2">
                        <div>
                          <label className="block text-white/40 mb-1">قیمت (تومان)</label>
                          <input type="number" required value={prodFormPrice} onChange={e => setProdFormPrice(Number(e.target.value))} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-center" />
                        </div>
                        <div>
                          <label className="block text-white/40 mb-1">درصد تخفیف</label>
                          <input type="number" value={prodFormDiscount} onChange={e => setProdFormDiscount(Number(e.target.value))} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-center" />
                        </div>
                      </div>
                      <div>
                        <label className="block text-white/40 mb-1">تصویر محصول (آدرس لینک یا آپلود فایل)</label>
                        <div className="space-y-2">
                          <input type="text" value={prodFormImage} onChange={e => setProdFormImage(e.target.value)} placeholder="https://..." className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white" />
                          <div className="relative flex items-center justify-center border border-dashed border-white/20 hover:border-[#c49b63]/60 rounded-xl py-2 bg-white/5 transition-all group cursor-pointer">
                            <input 
                              type="file" 
                              accept="image/*" 
                              onChange={handleImageUpload} 
                              className="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                            />
                            <div className="flex items-center gap-2 text-white/50 group-hover:text-[#c49b63] transition-colors">
                              <Plus className="w-4 h-4" />
                              <span className="text-[11px] font-bold">انتخاب و آپلود عکس محلی</span>
                            </div>
                          </div>
                          {prodFormImage && prodFormImage.startsWith('data:') && (
                            <div className="flex items-center gap-2 bg-emerald-500/15 border border-emerald-500/30 p-2 rounded-xl text-[10px] text-emerald-400">
                              <CheckCircle2 className="w-3.5 h-3.5 shrink-0" />
                              <span>تصویر محلی آپلود شده با موفقیت پیوست شد</span>
                            </div>
                          )}
                        </div>
                      </div>
                      <div>
                        <label className="block text-white/40 mb-1">توضیحات</label>
                        <textarea value={prodFormDesc} onChange={e => setProdFormDesc(e.target.value)} rows={2} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white" />
                      </div>
                      <div>
                        <label className="block text-white/40 mb-1">مواد تشکیل دهنده</label>
                        <input type="text" value={prodFormIngredients} onChange={e => setProdFormIngredients(e.target.value)} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white" />
                      </div>

                      <div className="grid grid-cols-2 gap-2 p-3 bg-white/5 rounded-xl border border-white/10">
                        <label className="flex items-center gap-1.5 cursor-pointer text-white/70 hover:text-white">
                          <input type="checkbox" checked={prodFormAvailable} onChange={e => setProdFormAvailable(e.target.checked)} className="rounded" />
                          <span>موجود</span>
                        </label>
                        <label className="flex items-center gap-1.5 cursor-pointer text-white/70 hover:text-white">
                          <input type="checkbox" checked={prodFormPopular} onChange={e => setProdFormPopular(e.target.checked)} className="rounded" />
                          <span>محبوب</span>
                        </label>
                        <label className="flex items-center gap-1.5 cursor-pointer text-white/70 hover:text-white">
                          <input type="checkbox" checked={prodFormNew} onChange={e => setProdFormNew(e.target.checked)} className="rounded" />
                          <span>جدید</span>
                        </label>
                        <label className="flex items-center gap-1.5 cursor-pointer text-white/70 hover:text-white">
                          <input type="checkbox" checked={prodFormVisible} onChange={e => setProdFormVisible(e.target.checked)} className="rounded" />
                          <span>نمایش منو</span>
                        </label>
                      </div>

                      <div className="flex gap-2 pt-2">
                        <button type="submit" className="flex-1 bg-[#c49b63] hover:bg-[#c49b63]/90 text-black py-2 rounded-xl font-bold transition-all shadow-md accent-glow">ذخیره محصول</button>
                        {editingProduct && (
                          <button type="button" onClick={() => { setEditingProduct(null); resetProductForm(); }} className="bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-xl font-bold transition-colors">لغو</button>
                        )}
                      </div>
                    </form>
                  </div>

                  {/* جدول محصولات */}
                  <div className="flex-1 glass rounded-3xl shadow-md overflow-hidden h-fit">
                    <div className="overflow-x-auto">
                      <table className="w-full text-right border-collapse text-xs table-auto md:table-fixed">
                        <thead>
                          <tr className="bg-white/5 border-b border-white/10 text-white/50 font-bold">
                            <th className="p-4 md:p-2 text-center w-12 md:w-12">عکس</th>
                            <th className="p-4 md:p-2 text-right">نام</th>
                            <th className="p-4 md:p-2 text-center md:w-20 hidden md:table-cell">دسته</th>
                            <th className="p-4 md:p-2 text-center md:w-28">قیمت پایه</th>
                            <th className="p-4 md:p-2 text-center md:w-16 hidden sm:table-cell">تخفیف</th>
                            <th className="p-4 md:p-2 text-center md:w-16">موجودی</th>
                            <th className="p-4 md:p-2 text-center md:w-14 hidden lg:table-cell">نمایش</th>
                            <th className="p-4 md:p-2 w-20 md:w-20 text-center">عملیات</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-white/10 font-semibold text-white/80">
                          {products.map(p => (
                            <tr key={p.id} className="hover:bg-white/5 transition-colors">
                              <td className="p-4 md:p-2 text-center"><img src={p.image} className="w-8 h-8 rounded-lg object-cover mx-auto" /></td>
                              <td className="p-4 md:p-2 font-bold text-white text-right truncate max-w-[120px]" title={p.name}>{p.name}</td>
                              <td className="p-4 md:p-2 text-center text-white/60 hidden md:table-cell">{categories.find(c => c.id === p.category_id)?.name || 'ناشناس'}</td>
                              <td className="p-4 md:p-2 text-center font-mono text-[#c49b63]">{toPersianDigits(p.price.toLocaleString())} تومان</td>
                              <td className="p-4 md:p-2 text-center text-red-400 hidden sm:table-cell">٪{toPersianDigits(p.discount)}</td>
                              <td className="p-4 md:p-2 text-center">
                                <span className={`px-2 py-0.5 rounded text-[10px] mx-auto block w-fit ${p.is_available ? 'bg-green-500/15 text-green-400 border border-green-500/30' : 'bg-red-500/15 text-red-400 border border-red-500/30'}`}>
                                  {p.is_available ? 'موجود' : 'ناموجود'}
                                </span>
                              </td>
                              <td className="p-4 md:p-2 text-center text-white/60 hidden lg:table-cell">{p.is_visible ? 'بله' : 'خیر'}</td>
                              <td className="p-4 md:p-2">
                                <div className="flex gap-1 justify-center">
                                  <button onClick={() => handleStartEditProduct(p)} className="bg-blue-500/15 hover:bg-blue-500/25 text-blue-400 p-2 rounded-lg transition-colors" title="ویرایش محصول"><Pencil className="w-3.5 h-3.5" /></button>
                                  <button onClick={() => handleDeleteProduct(p.id)} className="bg-red-500/15 hover:bg-red-500/25 text-red-400 p-2 rounded-lg transition-colors" title="حذف محصول"><Trash2 className="w-3.5 h-3.5" /></button>
                                </div>
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            )}

            {/* ۳. تب مدیریت دسته‌بندی‌ها */}
            {adminTab === 'categories' && (
              <div className="space-y-6">
                <div className="flex flex-col md:flex-row gap-6">
                  {/* فرم افزودن ویرایش */}
                  <div className="w-full md:w-80 glass p-5 rounded-3xl shrink-0 h-fit space-y-4">
                    <h3 className="text-sm font-black text-white border-b border-white/10 pb-2 mb-2">
                      {editingCategory ? 'ویرایش دسته‌بندی' : 'ایجاد دسته‌بندی جدید'}
                    </h3>
                    <form onSubmit={handleCategorySubmit} className="space-y-4 text-xs font-semibold">
                      <div>
                        <label className="block text-white/40 mb-1">نام دسته‌بندی *</label>
                        <input type="text" required value={catFormName} onChange={e => setCatFormName(e.target.value)} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white" />
                      </div>
                      <div>
                        <label className="block text-white/40 mb-1.5">انتخاب آیکون Font Awesome *</label>
                        <div className="grid grid-cols-4 gap-1.5 bg-white/5 border border-white/10 p-2 rounded-2xl max-h-36 overflow-y-auto mb-2 scrollbar-none">
                          {[
                            { id: 'fa-solid fa-mug-hot', label: 'گرم' },
                            { id: 'fa-solid fa-mug-saucer', label: 'اسپرسو' },
                            { id: 'fa-solid fa-ice-cream', label: 'بستنی' },
                            { id: 'fa-solid fa-leaf', label: 'دمنوش' },
                            { id: 'fa-solid fa-cake-candles', label: 'کیک/دسر' },
                            { id: 'fa-solid fa-glass-water', label: 'نوشیدنی' },
                            { id: 'fa-solid fa-egg', label: 'صبحانه' },
                            { id: 'fa-solid fa-burger', label: 'همبرگر' },
                            { id: 'fa-solid fa-pizza-slice', label: 'پیتزا' },
                            { id: 'fa-solid fa-cookie', label: 'کوکی' },
                            { id: 'fa-solid fa-wine-glass', label: 'سرو خاص' },
                            { id: 'fa-solid fa-stroopwafel', label: 'وافل' }
                          ].map(item => (
                            <button
                              key={item.id}
                              type="button"
                              onClick={() => setCatFormIcon(item.id)}
                              className={`p-1.5 rounded-xl flex flex-col items-center gap-1 border transition-all cursor-pointer ${catFormIcon === item.id ? 'bg-[#c49b63]/25 border-[#c49b63] text-white shadow-lg font-bold' : 'border-white/5 hover:bg-white/5 text-white/60 hover:text-white'}`}
                            >
                              {renderCategoryIcon(item.id, "text-base text-inherit")}
                              <span className="text-[8px] font-medium truncate w-full text-center">{item.label}</span>
                            </button>
                          ))}
                        </div>
                        <label className="block text-white/30 text-[9px] mb-1">یا نام کلاس Font Awesome اختصاصی را بنویسید:</label>
                        <input 
                          type="text" 
                          placeholder="مثال: fa-solid fa-coffee" 
                          value={catFormIcon} 
                          onChange={e => setCatFormIcon(e.target.value)} 
                          className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white font-mono text-center text-xs" 
                        />
                      </div>
                      <div>
                        <label className="block text-white/40 mb-1">ترتیب نمایش</label>
                        <input type="number" value={catFormOrder} onChange={e => setCatFormOrder(Number(e.target.value))} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-center" />
                      </div>

                      <div className="flex gap-2 pt-2">
                        <button type="submit" className="flex-1 bg-[#c49b63] hover:bg-[#c49b63]/90 text-black py-2 rounded-xl font-bold transition-all shadow-md accent-glow">ذخیره دسته</button>
                        {editingCategory && (
                          <button type="button" onClick={() => { setEditingCategory(null); setCatFormName(''); }} className="bg-white/10 hover:bg-white/20 text-white px-3 py-2 rounded-xl font-bold transition-colors">لغو</button>
                        )}
                      </div>
                    </form>
                  </div>

                  {/* جدول دسته‌بندی‌ها */}
                  <div className="flex-1 glass rounded-3xl shadow-md overflow-hidden h-fit">
                    <table className="w-full text-right border-collapse text-xs">
                      <thead>
                        <tr className="bg-white/5 border-b border-white/10 text-white/50 font-bold">
                          <th className="p-4 w-12">آیکون</th>
                          <th className="p-4">نام دسته‌بندی</th>
                          <th className="p-4">ترتیب نمایش</th>
                          <th className="p-4 w-24">عملیات ادمین</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-white/10 font-semibold text-white/80">
                        {categories.map(c => (
                          <tr key={c.id} className="hover:bg-white/5 transition-colors">
                            <td className="p-4">{renderCategoryIcon(c.icon)}</td>
                            <td className="p-4 font-bold text-white">{c.name}</td>
                            <td className="p-4">{toPersianDigits(c.sort_order)}</td>
                            <td className="p-4">
                              <div className="flex gap-1">
                                <button onClick={() => { setEditingCategory(c); setCatFormName(c.name); setCatFormIcon(c.icon); setCatFormOrder(c.sort_order); }} className="bg-blue-500/15 hover:bg-blue-500/25 text-blue-400 p-2 rounded-lg transition-colors" title="ویرایش"><Pencil className="w-3.5 h-3.5" /></button>
                                <button onClick={() => handleDeleteCategory(c.id)} className="bg-red-500/15 hover:bg-red-500/25 text-red-400 p-2 rounded-lg transition-colors" title="حذف"><Trash2 className="w-3.5 h-3.5" /></button>
                              </div>
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            )}

            {/* ۴. تب آرشیو سفارشات */}
            {adminTab === 'archive' && (
              <div className="glass p-6 rounded-3xl shadow-md">
                <h3 className="text-sm font-black text-white border-b border-white/10 pb-2 mb-4">آرشیو جامع فاکتورهای سفارش</h3>
                <div className="overflow-x-auto text-xs">
                  <table className="w-full text-right border-collapse">
                    <thead>
                      <tr className="bg-white/5 border-b border-white/10 text-white/50 font-bold">
                        <th className="p-4">کد سفارش</th>
                        <th className="p-4">تاریخ ثبت</th>
                        <th className="p-4">مشتری</th>
                        <th className="p-4">نوع تحویل</th>
                        <th className="p-4">جمع فاکتور</th>
                        <th className="p-4">وضعیت فاکتور</th>
                        <th className="p-4">عملیات چاپ فیش</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-white/10 font-semibold text-white/80">
                      {orders.map((o, idx) => (
                        <tr key={idx} className="hover:bg-white/5 transition-colors">
                          <td className="p-4 font-black text-white">{o.order_code}</td>
                          <td className="p-4">{toPersianDigits(o.created_jalali)}</td>
                          <td className="p-4">{o.customer_name} ({toPersianDigits(o.customer_phone)})</td>
                          <td className="p-4">{o.order_type === 'indoor' ? 'حضوری' : 'غیرحضوری'}</td>
                          <td className="p-4 font-black text-[#c49b63]">{toPersianDigits(o.total_amount.toLocaleString())} تومان</td>
                           <td className="p-4">
                            <div className="flex items-center">
                              {/* نسخه دسکتاپ: با نوشته و آیکون */}
                              <span className={`hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold ${o.status === 'completed' ? 'bg-green-500/15 text-green-400 border border-green-500/30' : (o.status === 'cancelled' ? 'bg-red-500/15 text-red-400 border border-red-500/30' : 'bg-amber-500/15 text-amber-400 border border-amber-500/30')}`}>
                                {o.status === 'completed' && <Check className="w-3.5 h-3.5" />}
                                {o.status === 'cancelled' && <Plus className="w-3.5 h-3.5 rotate-45" />}
                                {o.status !== 'completed' && o.status !== 'cancelled' && <RefreshCw className="w-3 h-3 animate-spin" style={{ animationDuration: '4s' }} />}
                                <span>{o.status === 'completed' ? 'تکمیل شده' : (o.status === 'cancelled' ? 'لغو شده' : 'در حال انجام')}</span>
                              </span>
                              {/* نسخه گوشی: فقط آیکون دایره‌ای بدون شکستن بوردر */}
                              <span className={`inline-flex md:hidden w-7 h-7 items-center justify-center rounded-full text-xs font-bold shrink-0 ${o.status === 'completed' ? 'bg-green-500/15 text-green-400 border border-green-500/30' : (o.status === 'cancelled' ? 'bg-red-500/15 text-red-400 border border-red-500/30' : 'bg-amber-500/15 text-amber-400 border border-amber-500/30')}`} title={o.status === 'completed' ? 'تکمیل شده' : (o.status === 'cancelled' ? 'لغو شده' : 'فعال')}>
                                {o.status === 'completed' && <Check className="w-3.5 h-3.5" />}
                                {o.status === 'cancelled' && <Plus className="w-3.5 h-3.5 rotate-45" />}
                                {o.status !== 'completed' && o.status !== 'cancelled' && <RefreshCw className="w-3.5 h-3.5" />}
                              </span>
                            </div>
                          </td>
                          <td className="p-4">
                            <button onClick={() => handlePrintInvoice(o)} className="bg-white/10 hover:bg-white/20 text-white p-2 rounded-lg transition-colors" title="چاپ فیش">
                              <Printer className="w-3.5 h-3.5" />
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {/* ۵. تب گزارشات مالی ادمین */}
            {adminTab === 'reports' && (
              <div className="space-y-6">
                <div className="glass p-6 rounded-3xl shadow-md flex flex-col md:flex-row items-center justify-between gap-4">
                  <div>
                    <h3 className="text-base font-black text-white">گزارشات فروش و تراکنش‌ها</h3>
                    <p className="text-xs text-white/40 mt-1">جمع درآمد کل از تراکنش‌ها، تعداد کل و صدور مستقیم فیش رسمی گزارشات به صورت PDF فارسی شیک</p>
                  </div>
                  <button 
                    onClick={handleExportPDF}
                    className="bg-red-600 hover:bg-red-500 text-white px-5 py-3 rounded-2xl font-bold text-xs flex items-center gap-2 shadow-lg shadow-red-600/20 transition-all cursor-pointer"
                  >
                    <Printer className="w-4 h-4" />
                    <span>خروجی فایل PDF رسمی (A4 فارسی)</span>
                  </button>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="glass p-6 rounded-3xl text-center space-y-2">
                    <span className="text-xs text-white/40 font-bold block">مجموع درآمدهای حاصله</span>
                    <span className="text-2xl font-black text-emerald-400 block">{toPersianDigits(salesToday.toLocaleString())} <span className="text-sm font-normal">تومان</span></span>
                  </div>
                  <div className="glass p-6 rounded-3xl text-center space-y-2">
                    <span className="text-xs text-white/40 font-bold block">کل فاکتورهای فعال ثبت شده</span>
                    <span className="text-2xl font-black text-white block">{toPersianDigits(orders.length)} فاکتور</span>
                  </div>
                </div>

                {/* نمودار روند فروش کافه */}
                <div className="glass p-6 rounded-3xl shadow-md space-y-4">
                  <div className="flex items-center justify-between">
                    <h4 className="text-sm font-black text-white flex items-center gap-2">
                      <BarChart3 className="w-4.5 h-4.5 text-[#c49b63]" />
                      <span>نمودار روند فروش کافه (تراکنش‌های موفق)</span>
                    </h4>
                    <span className="text-[10px] text-white/45 font-bold">بروزرسانی لحظه‌ای</span>
                  </div>

                  <div className="relative h-64 w-full flex items-center justify-center bg-black/20 rounded-2xl border border-white/5 p-4 overflow-hidden">
                    <svg className="w-full h-full" viewBox="0 0 500 180" preserveAspectRatio="none">
                      <defs>
                        <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                          <stop offset="0%" stopColor="#c49b63" stopOpacity="0.25" />
                          <stop offset="100%" stopColor="#c49b63" stopOpacity="0.0" />
                        </linearGradient>
                      </defs>
                      
                      {/* Grid Lines */}
                      {[0, 0.25, 0.5, 0.75, 1].map((ratio, idx) => (
                        <line 
                          key={idx}
                          x1="50" 
                          y1={15 + ratio * 120} 
                          x2="480" 
                          y2={15 + ratio * 120} 
                          stroke="rgba(255,255,255,0.05)" 
                          strokeWidth="1"
                        />
                      ))}
                      
                      {/* Area Under Curve */}
                      <path 
                        d={`M 50,135 ` + getSalesTrendData().map((d, idx) => {
                          const x = 50 + (idx / 6) * 430;
                          const maxS = Math.max(...getSalesTrendData().map(x => x.sales), 1000000);
                          const y = 15 + 120 - (d.sales / maxS) * 120;
                          return `L ${x},${y}`;
                        }).join(' ') + ` L 480,135 Z`}
                        fill="url(#chartGrad)"
                      />
                      
                      {/* Line Curve */}
                      <path 
                        d={getSalesTrendData().map((d, idx) => {
                          const x = 50 + (idx / 6) * 430;
                          const maxS = Math.max(...getSalesTrendData().map(x => x.sales), 1000000);
                          const y = 15 + 120 - (d.sales / maxS) * 120;
                          return `${idx === 0 ? 'M' : 'L'} ${x},${y}`;
                        }).join(' ')}
                        fill="none"
                        stroke="#c49b63"
                        strokeWidth="3.5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      />

                      {/* Dots and Labels */}
                      {getSalesTrendData().map((d, idx) => {
                        const x = 50 + (idx / 6) * 430;
                        const maxS = Math.max(...getSalesTrendData().map(x => x.sales), 1000000);
                        const y = 15 + 120 - (d.sales / maxS) * 120;
                        return (
                          <g key={idx}>
                            <circle 
                              cx={x} 
                              cy={y} 
                              r="5" 
                              fill="#141414" 
                              stroke="#c49b63" 
                              strokeWidth="2.5" 
                            />
                            {/* Value tooltip label (shows above dot) */}
                            <text 
                              x={x} 
                              y={y - 10} 
                              textAnchor="middle" 
                              fill="#c49b63" 
                              fontSize="8" 
                              fontWeight="bold"
                            >
                              {toPersianDigits(Math.round(d.sales / 1000) + 'k')}
                            </text>
                            {/* Day under line */}
                            <text 
                              x={x} 
                              y="155" 
                              textAnchor="middle" 
                              fill="rgba(255,255,255,0.4)" 
                              fontSize="9" 
                              fontWeight="bold"
                            >
                              {d.day}
                            </text>
                          </g>
                        );
                      })}
                    </svg>
                  </div>
                </div>
              </div>
            )}

            {/* ۶. تب تنظیمات ادمین */}
            {adminTab === 'settings' && (
              <div className="space-y-6 max-w-2xl mx-auto">
                <div className="glass p-6 rounded-3xl shadow-md space-y-6">
                  <h3 className="text-sm font-black text-white border-b border-white/10 pb-2">تنظیمات اصلی کافه</h3>
                  <div className="space-y-4 text-xs font-semibold">
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-white/40 mb-1.5">نام تجاری کافه *</label>
                        <input type="text" value={settings.cafe_name} onChange={e => setSettings({ ...settings, cafe_name: e.target.value })} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white focus:border-[#c49b63]" />
                      </div>
                      <div>
                        <label className="block text-white/40 mb-1.5">تلفن کافه *</label>
                        <input type="text" value={settings.cafe_phone} onChange={e => setSettings({ ...settings, cafe_phone: e.target.value })} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white text-center focus:border-[#c49b63]" />
                      </div>
                    </div>
                    <div>
                      <label className="block text-white/40 mb-1.5">ساعات کاری *</label>
                      <input type="text" value={settings.working_hours} onChange={e => setSettings({ ...settings, working_hours: e.target.value })} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white focus:border-[#c49b63]" />
                    </div>
                    <div>
                      <label className="block text-white/40 mb-1.5">توضیح کوتاه کافه</label>
                      <textarea value={settings.cafe_description} onChange={e => setSettings({ ...settings, cafe_description: e.target.value })} rows={2} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white focus:border-[#c49b63]" />
                    </div>
                    <div>
                      <label className="block text-white/40 mb-1.5">آدرس فیزیکی کافه</label>
                      <input type="text" value={settings.cafe_address} onChange={e => setSettings({ ...settings, cafe_address: e.target.value })} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white focus:border-[#c49b63]" />
                    </div>
                    <div>
                      <label className="block text-white/40 mb-1.5">لوگو یا نشان کافه (تصویر)</label>
                      <div className="flex flex-col sm:flex-row gap-4 items-center bg-white/5 border border-white/10 p-5 rounded-2xl">
                        {settings.logo_url ? (
                          <div className="relative w-20 h-20 rounded-2xl overflow-hidden border border-white/20 shrink-0 shadow-lg group">
                            <img src={settings.logo_url} alt="لوگو کافه" className="w-full h-full object-cover animate-fade-in" />
                            <button 
                              type="button"
                              onClick={() => setSettings({ ...settings, logo_url: '' })}
                              className="absolute inset-0 bg-black/70 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-red-400 cursor-pointer"
                              title="حذف لوگو"
                            >
                              <Trash2 className="w-5 h-5" />
                            </button>
                          </div>
                        ) : (
                          <div className="w-20 h-20 rounded-2xl bg-white/5 border-2 border-dashed border-white/10 flex items-center justify-center text-white/40 shrink-0">
                            <Coffee className="w-8 h-8" />
                          </div>
                        )}
                        <div className="flex-1 w-full text-center sm:text-right">
                          <p className="text-xs text-white/80 font-bold mb-1">تصویر لوگوی کافه را آپلود کنید</p>
                          <p className="text-[10px] text-white/40 mb-3.5">فرمت‌های مجاز: PNG, JPG, JPEG (حداکثر ۲ مگابایت)</p>
                          <label className="inline-flex items-center gap-2 bg-[#c49b63] hover:bg-[#c49b63]/90 text-black px-4 py-2.5 rounded-xl text-[11px] font-black cursor-pointer transition-all hover:scale-105 active:scale-95 shadow-md">
                            <Upload className="w-3.5 h-3.5" />
                            <span>انتخاب فایل تصویر</span>
                            <input 
                              type="file" 
                              accept="image/*" 
                              className="sr-only" 
                              onChange={(e) => {
                                const file = e.target.files?.[0];
                                if (file) {
                                  if (file.size > 2 * 1024 * 1024) {
                                    showNotification('حجم فایل نباید بیشتر از ۲ مگابایت باشد.', 'error');
                                    return;
                                  }
                                  const reader = new FileReader();
                                  reader.onloadend = () => {
                                    setSettings(prev => ({ ...prev, logo_url: reader.result as string }));
                                    showNotification('لوگوی کافه با موفقیت آپلود شد.', 'success');
                                  };
                                  reader.readAsDataURL(file);
                                }
                              }} 
                            />
                          </label>
                        </div>
                      </div>
                    </div>

                    <div className="pt-2 flex justify-end">
                      <button onClick={() => showNotification('تنظیمات کافه ذخیره شد.', 'success')} className="bg-[#c49b63] hover:bg-[#c49b63]/90 text-black px-6 py-2.5 rounded-xl font-bold transition-all shadow-md accent-glow">ثبت نهایی تنظیمات</button>
                    </div>
                  </div>
                </div>

                {/* پنل تغییر رمز عبور و نام کاربری ادمین */}
                <div className="glass p-6 rounded-3xl border border-white/10 shadow-md space-y-4">
                  <h3 className="text-sm font-black text-red-400 border-b border-white/10 pb-2 flex items-center gap-2">
                    <Lock className="w-4 h-4" />
                    <span>تنظیمات هویت و عبور امنیتی پنل مدیریت</span>
                  </h3>
                  <div className="space-y-4 text-xs font-semibold">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label className="block text-white/40 mb-1.5">نام کاربری جدید مدیریت *</label>
                        <input 
                          type="text" 
                          value={settings.admin_username || ''} 
                          onChange={e => setSettings({ ...settings, admin_username: e.target.value })} 
                          className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white font-mono focus:border-[#c49b63] focus:ring-1 focus:ring-[#c49b63]/30 text-center" 
                          placeholder="نام کاربری..."
                        />
                      </div>
                      <div>
                        <label className="block text-white/40 mb-1.5">رمز عبور جدید مدیریت *</label>
                        <input 
                          type="password" 
                          value={settings.admin_password || ''} 
                          onChange={e => setSettings({ ...settings, admin_password: e.target.value })} 
                          className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white font-mono focus:border-[#c49b63] focus:ring-1 focus:ring-[#c49b63]/30 text-center" 
                          placeholder="رمز عبور امنیتی..."
                        />
                      </div>
                    </div>
                    <span className="text-[10px] text-white/40 block mt-1">این اطلاعات برای احراز هویت در ریشه مسیر <code className="text-[#c49b63] font-mono">#admin</code> استفاده می‌شود.</span>

                    <div className="pt-2 flex justify-end">
                      <button onClick={() => showNotification('اطلاعات امنیتی مدیریت با موفقیت بروزرسانی شد.', 'success')} className="bg-red-500/15 hover:bg-red-500/25 text-red-400 border border-red-500/30 px-6 py-2.5 rounded-xl font-bold transition-all shadow-md">ذخیره هویت و رمز عبور جدید</button>
                    </div>
                  </div>
                </div>
              </div>
            )}
              </div>
            </main>
        </div>
        )
      )}

      {/* =================================================================== */}
      {/* نمای ۳: کدهای سی‌پنل و مرکز دانلود */}
      {/* =================================================================== */}
      {appMode === 'code' && (
        <div className="flex-1 flex flex-col bg-[#0f0e0c] text-white animate-slide-up">
          {/* هدر یکپارچه جدید بخش کدها */}
          <div className="sticky top-0 z-50 bg-[#131210]/95 backdrop-blur-md border-b border-white/10 px-4 py-3 flex items-center justify-between gap-4 shadow-lg">
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 rounded-full bg-[#c49b63]/10 flex items-center justify-center text-[#c49b63] border border-[#c49b63]/25">
                <Folder className="w-5 h-5 animate-pulse" />
              </div>
              <div>
                <h1 className="text-sm font-black text-white">مرکز دانلود کدهای cPanel</h1>
                <p className="text-[10px] text-white/40 font-bold font-mono">پکیج آماده وب‌سایت PHP</p>
              </div>
            </div>

            <div className="flex items-center gap-1.5">
              <button 
                onClick={() => { setAppMode('customer'); window.location.hash = ''; }}
                className="px-3 md:px-3.5 py-1.5 md:py-2 rounded-xl text-[10px] md:text-xs font-bold transition-all bg-white/5 hover:bg-white/10 border border-white/10 text-white flex items-center gap-1 cursor-pointer"
              >
                <ExternalLink className="w-3.5 h-3.5" />
                <span>📱 منوی مشتری</span>
              </button>
              <button 
                onClick={() => { setAppMode('admin'); window.location.hash = '#admin'; }}
                className="px-3 md:px-3.5 py-1.5 md:py-2 rounded-xl text-[10px] md:text-xs font-bold transition-all bg-[#c49b63] hover:bg-[#b28b58] text-black flex items-center gap-1 cursor-pointer font-black"
              >
                <LayoutDashboard className="w-3.5 h-3.5" />
                <span>🛠️ پنل مدیریت</span>
              </button>
            </div>
          </div>

          <div className="flex-1 flex flex-col md:flex-row bg-transparent">
          
          {/* مرورگر دایرکتوری درگاه کدهای سی پنل */}
          <div className="w-full md:w-80 border-b md:border-b-0 md:border-l border-white/10 p-4 md:p-6 shrink-0 h-fit space-y-4 md:space-y-6 bg-[#131210]/60 backdrop-blur-md">
            <div>
              <h3 className="text-base font-black text-white flex items-center gap-2">
                <Folder className="text-[#c49b63]" />
                <span>دایرکتوری فایل‌های پروژه‌</span>
              </h3>
              <p className="text-[10px] text-white/50 mt-1">ساختار فایل استاندارد برای آپلود آسان روی cPanel</p>
            </div>

            {/* موبایل: دراپ‌دان مدرن انتخاب فایل برای صرفه‌جویی در فضا */}
            <div className="block md:hidden">
              <label className="block text-[10px] font-bold text-white/40 mb-2">انتخاب فایل جهت مشاهده و کپی کد:</label>
              <select 
                value={selectedFile}
                onChange={e => setSelectedFile(e.target.value)}
                className="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-xs font-black text-white cursor-pointer focus:border-[#c49b63] outline-none"
              >
                {[
                  { name: 'فایل‌های روت پروژه', files: ['index.php', 'login.php', 'logout.php', '.htaccess', 'README.md'] },
                  { name: 'پوشه تنظیمات (config/)', files: ['config/config.php'] },
                  { name: 'پوشه کتابخانه‌ها (functions/)', files: ['functions/jdf.php'] },
                  { name: 'پوشه پایگاه داده (database/)', files: ['database/schema.sql'] },
                  { name: 'پوشه قالب صفحات (includes/)', files: ['includes/header.php', 'includes/footer.php'] },
                  { name: 'پوشه سفارشات AJAX (api/)', files: ['api/submit_order.php'] },
                  { name: 'پوشه پنل ادمین (admin/)', files: [
                    'admin/dashboard.php', 'admin/orders.php', 'admin/products.php', 
                    'admin/categories.php', 'admin/settings.php', 'admin/reports.php',
                    'admin/api/get_new_orders.php', 'admin/api/update_order_status.php'
                  ] }
                ].map((folder, idx) => (
                  <optgroup key={idx} label={folder.name} className="bg-[#131210] text-white font-sans text-xs">
                    {folder.files.map(file => (
                      <option key={file} value={file} className="bg-[#131210] text-white font-mono">{file}</option>
                    ))}
                  </optgroup>
                ))}
              </select>
            </div>

            {/* دسکتاپ: فایل‌های پروژه به صورت دسته‌بندی درختی در مرورگر */}
            <div className="hidden md:block space-y-4 max-h-[450px] overflow-y-auto text-xs font-semibold">
              {[
                { name: 'فایل‌های روت پروژه', files: ['index.php', 'login.php', 'logout.php', '.htaccess', 'README.md'] },
                { name: 'پوشه تنظیمات (config/)', files: ['config/config.php'] },
                { name: 'پوشه کتابخانه‌ها (functions/)', files: ['functions/jdf.php'] },
                { name: 'پوشه پایگاه داده (database/)', files: ['database/schema.sql'] },
                { name: 'پوشه قالب صفحات (includes/)', files: ['includes/header.php', 'includes/footer.php'] },
                { name: 'پوشه سفارشات AJAX (api/)', files: ['api/submit_order.php'] },
                { name: 'پوشه پنل ادمین (admin/)', files: [
                  'admin/dashboard.php', 'admin/orders.php', 'admin/products.php', 
                  'admin/categories.php', 'admin/settings.php', 'admin/reports.php',
                  'admin/api/get_new_orders.php', 'admin/api/update_order_status.php'
                ] }
              ].map((folder, idx) => (
                <div key={idx} className="space-y-1 bg-white/5 p-3 rounded-xl border border-white/10">
                  <span className="text-[10px] text-[#c49b63]/85 block mb-1.5">{folder.name}</span>
                  {folder.files.map(file => (
                    <button
                      key={file}
                      onClick={() => setSelectedFile(file)}
                      className={`w-full flex items-center gap-2 px-3 py-1.5 rounded-lg text-right font-mono text-[11px] transition-colors ${selectedFile === file ? 'bg-[#c49b63]/15 text-[#c49b63] font-bold border-r-2 border-[#c49b63]' : 'text-white/60 hover:bg-white/5 hover:text-white'}`}
                    >
                      <File className="w-3.5 h-3.5 shrink-0" />
                      <span className="truncate">{file}</span>
                    </button>
                  ))}
                </div>
              ))}
            </div>

            {/* دکمه دانلود کل پکیج زیپ با استایل سازگار با تم سایت */}
            <div className="pt-2">
              <button 
                onClick={handleDownloadCPanelPackage}
                disabled={isZipping}
                className="w-full bg-[#c49b63] hover:bg-[#b28b58] text-black disabled:opacity-50 py-3.5 md:py-4 rounded-2xl font-black text-xs md:text-sm flex items-center justify-center gap-2 shadow-lg shadow-[#c49b63]/10 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer"
              >
                <Download className="w-4 h-4 md:w-5 md:h-5 animate-bounce text-black" />
                <span>{isZipping ? 'در حال زیپ کردن پکیج...' : 'دانلود پکیج کامل زیپ سی‌پنل'}</span>
              </button>
              <span className="text-[9px] text-white/40 block text-center mt-2 leading-relaxed">تنها با یک کلیک، کل پروژه PHP + فایل SQL دیتابیس را به صورت مستقیم دانلود و روی هاست خود آپلود کنید.</span>
            </div>
          </div>

          {/* ویرایشگر کدهای PHP ادمین با قابلیت کپی کدهای هر فایل و ریسپانسیو بی‌نقص */}
          <div className="flex-1 p-4 md:p-6 flex flex-col h-[450px] md:h-[650px] min-w-0 w-full max-w-full overflow-hidden">
            <div className="bg-black/40 backdrop-blur-md rounded-2xl border border-white/10 flex flex-col flex-1 overflow-hidden relative shadow-2xl min-w-0 w-full">
              <div className="px-4 md:px-5 py-3 bg-black/30 border-b border-white/10 flex items-center justify-between shrink-0">
                <div className="flex items-center gap-2 font-mono text-xs text-white min-w-0">
                  <div className="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0"></div>
                  <div className="w-2.5 h-2.5 rounded-full bg-yellow-500 shrink-0"></div>
                  <div className="w-2.5 h-2.5 rounded-full bg-green-500 shrink-0"></div>
                  <span className="ml-2 text-white/40 hidden sm:inline">Editor:</span>
                  <span className="text-[#c49b63] font-bold text-[11px] md:text-xs truncate">{selectedFile}</span>
                </div>
                
                <button 
                  onClick={() => handleCopyCode(phpFiles[selectedFile] || '')}
                  className="bg-white/10 hover:bg-white/20 text-white text-[10px] md:text-xs px-3 py-1.5 rounded-lg flex items-center gap-1 font-bold transition-all cursor-pointer shrink-0"
                >
                  <Copy className="w-3.5 h-3.5" />
                  <span>{copiedFile ? 'کپی شد!' : 'کپی کد فایل'}</span>
                </button>
              </div>

              {/* کدها با پشتیبانی از اسکرول داخلی مجزا */}
              <div className="flex-1 overflow-auto p-4 md:p-6 font-mono text-[10px] md:text-xs leading-relaxed text-white/80 select-all bg-black/20 w-full max-w-full">
                <pre className="whitespace-pre-wrap break-all sm:whitespace-pre sm:break-normal overflow-x-auto">{phpFiles[selectedFile] || 'در حال لود کردن کدهای فایل...'}</pre>
              </div>
            </div>
          </div>
          </div>
        </div>
      )}

      {/* =================================================================== */}
      {/* مودال‌های کلاینت که خارج از کانتینر اصلی متحرک رندر می‌شوند تا افکت فیکسد ۱۰۰٪ دقیق کار کند */}
      {/* =================================================================== */}
      {appMode === 'customer' && selectedProductDetail && (
        <div className="fixed inset-0 z-[300] flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
          <div className="bg-[#131210] border border-white/10 rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl relative animate-slide-up">
            <button onClick={() => setSelectedProductDetail(null)} className="absolute top-4 left-4 bg-white/10 hover:bg-white/20 p-2 rounded-full text-white shadow-md z-10 transition-colors cursor-pointer">
              <Plus className="w-5 h-5 rotate-45" />
            </button>

            <div className="w-full h-56 md:h-64 bg-stone-900 relative">
              <img src={selectedProductDetail.image} alt={selectedProductDetail.name} className="w-full h-full object-cover" />
              <div className="absolute inset-0 bg-gradient-to-t from-[#131210] to-transparent"></div>
              <h3 className="absolute bottom-5 right-5 text-xl font-black text-white">{selectedProductDetail.name}</h3>
            </div>

            <div className="p-6">
              <h4 className="text-xs font-bold text-white/40 mb-1">توضیحات محصول</h4>
              <p className="text-sm text-white/80 leading-relaxed mb-4">{selectedProductDetail.description}</p>

              {selectedProductDetail.ingredients && (
                <div className="mb-4">
                  <h4 className="text-xs font-bold text-white/40 mb-1">مواد تشکیل‌دهنده</h4>
                  <p className="text-sm text-white/80 leading-relaxed bg-white/5 p-3 rounded-2xl border border-white/10">{selectedProductDetail.ingredients}</p>
                </div>
              )}

              <div className="flex items-center justify-between border-t border-white/10 pt-4 mt-6">
                <div className="flex flex-col">
                  <span className="text-xs text-white/40">قیمت نهایی</span>
                  <span className="text-lg font-black text-[#c49b63]">{toPersianDigits((selectedProductDetail.price - (selectedProductDetail.price * selectedProductDetail.discount / 100)).toLocaleString())} تومان</span>
                </div>
                {selectedProductDetail.is_available ? (
                  <button 
                    onClick={() => { addToCart(selectedProductDetail); setSelectedProductDetail(null); }}
                    className="bg-[#c49b63] hover:bg-[#c49b63]/90 text-black py-2 px-5 rounded-xl font-bold flex items-center gap-1.5 transition-all shadow-md accent-glow cursor-pointer"
                  >
                    <ShoppingBag className="w-4 h-4" />
                    <span>افزودن به سبد</span>
                  </button>
                ) : (
                  <button disabled className="bg-white/5 text-white/30 py-2 px-5 rounded-xl font-bold cursor-not-allowed">ناموجود</button>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {appMode === 'customer' && isCartOpen && (
        <>
          {/* پشت زمینه تاریک */}
          <div 
            onClick={() => setIsCartOpen(false)} 
            className="fixed inset-0 bg-black/80 backdrop-blur-md z-[300]" 
          />
          
          {/* منوی سبد خرید و فرم سفارش با استایل مدرن و ۱۰۰٪ هماهنگ با تم تاریک و لوکس کافه */}
          <div className="fixed z-[301] flex flex-col bg-[#131210] text-white shadow-[0_-4px_35px_rgba(0,0,0,0.6)] border-t md:border-t-0 md:border-l border-white/10 transition-all duration-200 p-0
                         bottom-0 right-0 left-0 h-[85vh] max-h-[85vh] rounded-t-[25px] animate-slide-up
                         md:top-0 md:bottom-0 md:left-auto md:right-0 md:w-[400px] md:h-screen md:max-h-screen md:rounded-l-[25px] md:rounded-tr-none md:animate-slide-left">
            
            {checkoutStep === 1 ? (
              <>
                <div className="p-5 border-b border-white/10 flex items-center justify-between bg-black/20">
                  <div className="flex items-center gap-2">
                    <ShoppingBag className="text-[#c49b63]" />
                    <h3 className="text-base font-black text-white">سبد خرید شما</h3>
                  </div>
                  <button onClick={() => setIsCartOpen(false)} className="p-1.5 rounded-full hover:bg-white/10 text-white/60 hover:text-white cursor-pointer transition-colors">
                    <Plus className="w-6 h-6 rotate-45" />
                  </button>
                </div>

                <div className="flex-1 overflow-y-auto p-5 space-y-4">
                  {cart.map(item => (
                    <div key={item.id} className="bg-white/5 p-3.5 rounded-2xl flex items-center gap-3 border border-white/10">
                      {item.image ? (
                        <img src={item.image} className="w-14 h-14 rounded-xl object-cover shrink-0" />
                      ) : (
                        <div className="w-14 h-14 bg-white/5 rounded-xl flex items-center justify-center shrink-0">
                          <i className="fa-solid fa-mug-hot text-white/30" />
                        </div>
                      )}
                      <div className="flex-1 min-w-0">
                        <h4 className="text-xs md:text-sm font-bold text-white truncate">{item.name}</h4>
                        <span className="text-[11px] md:text-xs font-semibold text-[#c49b63] block mt-0.5">{toPersianDigits(item.price.toLocaleString())} تومان</span>
                      </div>
                      <div className="flex flex-col items-end gap-2 shrink-0">
                        <div className="flex items-center gap-2.5 bg-white/5 border border-white/10 px-2.5 py-1.5 rounded-xl shadow-sm text-white">
                          <button onClick={() => updateCartQty(item.id, 1)} className="text-white/60 hover:text-white text-xs cursor-pointer transition-colors"><Plus className="w-3 h-3" /></button>
                          <span className="text-xs font-bold font-mono text-[#c49b63]">{toPersianDigits(item.quantity)}</span>
                          <button onClick={() => updateCartQty(item.id, -1)} className="text-white/60 hover:text-white text-xs cursor-pointer transition-colors"><Minus className="w-3 h-3" /></button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>

                <div className="p-5 border-t border-white/10 bg-black/40 space-y-4">
                  <div className="flex items-center justify-between text-sm font-semibold">
                    <span className="text-white/50">جمع کل سبد:</span>
                    <span className="font-black text-white text-base">{toPersianDigits(cart.reduce((a, b) => a + (b.price * b.quantity), 0).toLocaleString())} تومان</span>
                  </div>
                  <button onClick={() => setCheckoutStep(2)} className="w-full bg-[#c49b63] hover:bg-[#c49b63]/90 text-black py-3.5 rounded-2xl font-black flex items-center justify-center gap-2 shadow-lg transition-all active:scale-98 cursor-pointer accent-glow">
                    <span>ثبت سفارش و پرداخت</span>
                    <ChevronRight className="w-5 h-5 rotate-180" />
                  </button>
                </div>
              </>
            ) : (
              // فرم ثبت آدرس و موبایل مشتری
              <div className="flex flex-col h-full overflow-hidden">
                <div className="p-5 border-b border-white/10 flex items-center justify-between bg-black/20">
                  <div className="flex items-center gap-2">
                    <button onClick={() => setCheckoutStep(1)} className="p-1 rounded-full hover:bg-white/10 text-white/70 cursor-pointer transition-colors">
                      <ArrowRight className="w-5 h-5" />
                    </button>
                    <h4 className="font-black text-white text-sm md:text-base">اطلاعات سفارش‌دهنده</h4>
                  </div>
                  <button onClick={() => setIsCartOpen(false)} className="p-1.5 rounded-full hover:bg-white/10 text-white/60 hover:text-white cursor-pointer transition-colors">
                    <Plus className="w-6 h-6 rotate-45" />
                  </button>
                </div>

                <form id="checkout-form" onSubmit={handleCheckoutSubmit} className="flex-1 overflow-y-auto p-5 space-y-4">
                  <div>
                    <label className="block text-xs font-bold text-white/40 mb-2">نوع تحویل سفارش</label>
                    <div className="grid grid-cols-2 gap-3">
                      <label className="cursor-pointer">
                        <input type="radio" name="order_type" checked={orderType === 'outdoor'} onChange={() => setOrderType('outdoor')} className="sr-only" />
                        <div className={`p-2.5 text-center border-2 rounded-2xl font-bold text-xs transition-all ${orderType === 'outdoor' ? 'border-[#c49b63] bg-[#c49b63]/10 text-[#c49b63]' : 'border-white/10 text-white/50 bg-white/5'}`}>
                          غیرحضوری (ارسال)
                        </div>
                      </label>
                      <label className="cursor-pointer">
                        <input type="radio" name="order_type" checked={orderType === 'indoor'} onChange={() => setOrderType('indoor')} className="sr-only" />
                        <div className={`p-2.5 text-center border-2 rounded-2xl font-bold text-xs transition-all ${orderType === 'indoor' ? 'border-[#c49b63] bg-[#c49b63]/10 text-[#c49b63]' : 'border-white/10 text-white/50 bg-white/5'}`}>
                          حضوری در کافه
                        </div>
                      </label>
                    </div>
                  </div>

                  {orderType === 'indoor' ? (
                    // فیلدهای حضوری در کافه درخواستی طبق منطق دیتابیس cpanel-php
                    <div className="space-y-4 animate-fade-in">
                      <div className="grid grid-cols-2 gap-3">
                        <div>
                          <label className="block text-[10px] font-bold text-white/40 mb-1">نام *</label>
                          <input type="text" required value={firstName} onChange={e => setFirstName(e.target.value)} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:outline-none focus:border-[#c49b63] transition-colors" />
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-white/40 mb-1">نام خانوادگی *</label>
                          <input type="text" required value={lastName} onChange={e => setLastName(e.target.value)} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:outline-none focus:border-[#c49b63] transition-colors" />
                        </div>
                      </div>
                      <div>
                        <label className="block text-[10px] font-bold text-white/40 mb-1">شماره موبایل *</label>
                        <input type="text" required placeholder="09xxxxxxxxx" value={phone} onChange={e => setPhone(e.target.value)} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:outline-none focus:border-[#c49b63] transition-colors dir-ltr text-right" />
                      </div>
                      <div>
                        <label className="block text-[10px] font-bold text-white/40 mb-1">آدرس کامل *</label>
                        <textarea required value={address} onChange={e => setAddress(e.target.value)} rows={2} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:outline-none focus:border-[#c49b63] transition-colors" />
                      </div>
                      <div className="grid grid-cols-3 gap-2">
                        <div>
                          <label className="block text-[10px] font-bold text-white/40 mb-1">پلاک *</label>
                          <input type="text" required value={plaque} onChange={e => setPlaque(e.target.value)} className="w-full px-2 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:outline-none focus:border-[#c49b63] transition-colors text-center" />
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-white/40 mb-1">طبقه *</label>
                          <input type="text" required value={floor} onChange={e => setFloor(e.target.value)} className="w-full px-2 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:outline-none focus:border-[#c49b63] transition-colors text-center" />
                        </div>
                        <div>
                          <label className="block text-[10px] font-bold text-white/40 mb-1">واحد *</label>
                          <input type="text" required value={unit} onChange={e => setUnit(e.target.value)} className="w-full px-2 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:outline-none focus:border-[#c49b63] transition-colors text-center" />
                        </div>
                      </div>
                      <div>
                        <label className="block text-[10px] font-bold text-white/40 mb-1">توضیحات سفارش</label>
                        <textarea value={description} onChange={e => setDescription(e.target.value)} placeholder="توضیحات مربوط به زنگ، شماره میز و نحوه تحویل..." rows={2} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:outline-none focus:border-[#c49b63] transition-colors" />
                      </div>
                    </div>
                  ) : (
                    // فیلدهای غیرحضوری (ارسال) درخواستی طبق منطق دیتابیس cpanel-php
                    <div className="space-y-4 animate-fade-in">
                      <div>
                        <label className="block text-[10px] font-bold text-white/40 mb-1">نام کامل *</label>
                        <input type="text" required value={firstName} onChange={e => setFirstName(e.target.value)} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:outline-none focus:border-[#c49b63] transition-colors" />
                      </div>
                      {/* فیلد شماره موبایل برای غیرحضوری طبق درخواست مشتری حذف شده است */}
                      <div>
                        <label className="block text-[10px] font-bold text-white/40 mb-1">توضیحات ارسال</label>
                        <textarea value={description} onChange={e => setDescription(e.target.value)} placeholder="مثلا: لاته بدون شکر باشد." rows={3} className="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-xs text-white focus:outline-none focus:border-[#c49b63] transition-colors" />
                      </div>
                    </div>
                  )}
                </form>

                <div className="p-5 border-t border-white/10 bg-black/40">
                  <button 
                    form="checkout-form"
                    type="submit" 
                    className="w-full bg-[#c49b63] hover:bg-[#c49b63]/90 text-black py-3.5 rounded-2xl font-black flex items-center justify-center gap-2 shadow-lg transition-all cursor-pointer active:scale-98 accent-glow"
                  >
                    <Check className="w-5 h-5" />
                    <span>ثبت و پرداخت نهایی سفارش</span>
                  </button>
                </div>
              </div>
            )}
          </div>
        </>
      )}

      {/* فلوتینگ سبد خرید در منوی کلاینت با استایل لوکس، واکنش‌گرا و ۱۰۰٪ فیکسد واقعی در روت بدون تداخل */}
      {appMode === 'customer' && cart.length > 0 && (
        <button 
          onClick={() => { setIsCartOpen(true); setCheckoutStep(1); }}
          className="fixed z-50 bg-[#c49b63] hover:bg-[#b28b58] text-black font-black rounded-full shadow-[rgba(196,155,99,0.35)_0px_8px_32px] flex items-center gap-2 transition-all duration-300 hover:scale-105 active:scale-95 cursor-pointer
                     bottom-6 left-6 right-6 py-4 px-6 text-sm justify-center md:w-auto
                     md:left-auto md:right-8 md:bottom-8 md:px-6 md:py-4 md:text-base accent-glow animate-fade-in"
          title="مشاهده سبد خرید و ثبت نهایی"
        >
          <ShoppingBag className="w-5 h-5 shrink-0" />
          <span>مشاهده سبد خرید</span>
          <span className="bg-black text-[#c49b63] border border-[#c49b63]/30 text-[10px] font-black w-5.5 h-5.5 rounded-full flex items-center justify-center shadow-sm shrink-0 font-mono">
            {toPersianDigits(cart.reduce((a, b) => a + b.quantity, 0))}
          </span>
        </button>
      )}

      {/* سیستم اعلانات کاستوم شناور برنامه */}
      {notification && (
        <div className={`fixed bottom-5 right-5 z-50 flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl text-xs font-bold border backdrop-blur-xl transform transition-all duration-300 animate-slide-up ${
          notification.type === 'success' ? 'bg-[#1e2a22]/90 border-emerald-500/30 text-emerald-300' :
          notification.type === 'error' ? 'bg-[#2a1e1e]/90 border-red-500/30 text-red-300' :
          'bg-[#1e222a]/90 border-blue-500/30 text-blue-300'
        }`}>
          {notification.type === 'success' && <CheckCircle2 className="w-5 h-5 text-emerald-400" />}
          {notification.type === 'error' && <AlertTriangle className="w-5 h-5 text-red-400" />}
          {notification.type === 'info' && <Info className="w-5 h-5 text-blue-400" />}
          <span>{notification.text}</span>
        </div>
      )}
    </div>
  );
}
