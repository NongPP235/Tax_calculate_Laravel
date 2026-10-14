@php
    $value = fn ($name, $default = 0) => old($name, $form[$name] ?? $default);
    $socialCap = (int) $value('tax_year', 2569) === 2569 ? 10500 : 9000;
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำนวณภาษีเงินได้บุคคลธรรมดา</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calculate.css') }}?v=2">
</head>
<body>
    <header>
        <h1>Personal Tax Calculator</h1>
        <nav>
            <a href="/">หน้าหลัก</a>
            <a href="/knowledge">ความรู้เกี่ยวกับภาษี</a>
            <a href="/about">เกี่ยวกับ</a>
            @guest
                <a href="{{ route('login') }}">เข้าสู่ระบบ</a>
                <a href="{{ route('register') }}">สมัครสมาชิก</a>
            @endguest
            @auth
                <a href="{{ route('member.index') }}">ส่วนสมาชิก</a>
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">จัดการระบบ (Admin)</a>
                @endif
                <span>👤 {{ Auth::user()->name }}</span>
                @if(Route::has('logout'))
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit">ออกจากระบบ</button>
                    </form>
                @endif
            @endauth
        </nav>
    </header>
    <main>
        <form action="{{ route('tax.calculate') }}" method="post" id="tax-form">
            @csrf
            @if($errors->any())
                <div class="validation-errors" role="alert" tabindex="-1" id="validation-errors">
                    <strong>ยังคำนวณไม่ได้ กรุณาตรวจข้อมูลต่อไปนี้</strong>
                    <ul>
                        @foreach($errors->messages() as $field => $messages)
                            @foreach($messages as $message)
                                <li><a href="#{{ $field }}">{{ $message }}</a></li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            @endif
            <h3>1. ข้อมูลส่วนตัวและสิทธิพื้นฐาน</h3>
            <label for="tax_year">ปีภาษี (รายได้ทั้งปี)</label>
            <select name="tax_year" id="tax_year">
                @foreach([2569, 2568] as $year)
                    <option value="{{ $year }}" @selected($value('tax_year', 2569) == $year)>{{ $year }}</option>
                @endforeach
            </select>
            <p class="field-help">คำนวณสำหรับบุคคลธรรมดาที่แยกยื่นภาษี รองรับสิทธิที่ระบุในฟอร์มนี้</p>
            <label for="married-status">สถานภาพสมรส</label>
            <select name="married-status" id="married-status">
                @foreach(['single' => 'โสด', 'married_no_income' => 'จดทะเบียนสมรส คู่สมรสไม่มีเงินได้', 'married_has_income' => 'จดทะเบียนสมรส คู่สมรสมีเงินได้ / แยกยื่น'] as $key => $label)
                    <option value="{{ $key }}" @selected($value('married-status', 'single') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <fieldset>
                <legend>สิทธิลดหย่อนบุตร</legend>
                @foreach(['child_count' => 'จำนวนบุตรชอบด้วยกฎหมายที่มีสิทธิ (คนละ 30,000 บาท)', 'child_bonus_count' => 'ในจำนวนข้างต้น บุตรคนที่ 2 เป็นต้นไปที่เกิดตั้งแต่ปี 2561 (เพิ่มคนละ 30,000 บาท)', 'adopted_child_count' => 'จำนวนบุตรบุญธรรมที่จดทะเบียนและมีสิทธิ (คนละ 30,000 บาท)'] as $name => $label)
                    <label for="{{ $name }}">{{ $label }}</label>
                    <input type="number" name="{{ $name }}" id="{{ $name }}" min="0" step="1" value="{{ $value($name) }}">
                @endforeach
                <p class="field-help">นับเฉพาะบุตรที่เข้าเกณฑ์: ผู้เยาว์ หรืออายุไม่เกิน 25 ปีที่ศึกษาในมหาวิทยาลัย/อุดมศึกษา หรือศาลสั่งเป็นคนไร้ความสามารถ/เสมือนไร้ความสามารถและอยู่ในอุปการะ ต้องไม่มีเงินได้ที่ไม่ได้รับยกเว้นตั้งแต่ 30,000 บาทขึ้นไปในปีภาษี หากใช้สิทธิบุตรบุญธรรม จำนวนที่ใช้สิทธิรวมบุตรชอบด้วยกฎหมายต้องไม่เกิน 3 คน; บุตรชอบด้วยกฎหมายตั้งแต่ 3 คนใช้สิทธิบุตรบุญธรรมเพิ่มไม่ได้</p>
                <label class="check-label" for="children_confirmed"><input type="checkbox" name="children_confirmed" id="children_confirmed" value="1" @checked($value('children_confirmed'))> ยืนยันว่าบุตรที่นับเข้าเงื่อนไข และบุตรที่ใช้สิทธิเพิ่มเป็นบุตรชอบด้วยกฎหมายคนที่ 2 เป็นต้นไปตามลำดับการเกิด</label>
            </fieldset>
            <fieldset>
                <legend>สิทธิผู้มีเงินได้พิการ / ผู้สูงอายุ</legend>
                <label for="self_exemption">สิทธิยกเว้นเงินได้ของตนเอง</label>
                <select name="self_exemption" id="self_exemption">
                    @foreach(['none' => 'ไม่ใช้สิทธินี้', 'disabled' => 'ผู้มีเงินได้พิการ (ยกเว้นไม่เกิน 190,000 บาท)', 'elderly' => 'ผู้มีเงินได้อายุ 65 ปีขึ้นไป (ยกเว้นไม่เกิน 190,000 บาท)'] as $key => $label)
                        <option value="{{ $key }}" @selected($value('self_exemption', 'none') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <div id="self-exemption-details">
                    <label for="taxpayer_age">อายุผู้มีเงินได้ในปีภาษี</label>
                    <input type="number" name="taxpayer_age" id="taxpayer_age" min="0" max="120" step="1" value="{{ $value('taxpayer_age') }}">
                    <label for="exemption_income_type">เลือกเงินได้ที่จะใช้สิทธิยกเว้น</label>
                    <select name="exemption_income_type" id="exemption_income_type">
                        @foreach(range(1, 8) as $group)
                            <option value="{{ $group }}" @selected($value('exemption_income_type', 1) == $group)>ม.40({{ $group }})</option>
                        @endforeach
                    </select>
                    <p class="field-help">เครื่องมือนี้ใช้สิทธิในเงินได้ประเภทที่เลือกเพียงประเภทเดียว โดยไม่เกินรายได้ประเภทนั้น และหักก่อนคำนวณค่าใช้จ่าย อายุ 65 ปีเลือกได้เพียงสิทธิเดียว</p>
                    <label class="check-label" for="resident_confirmed">
                        <input type="checkbox" name="resident_confirmed" id="resident_confirmed" value="1" @checked($value('resident_confirmed'))> เป็นผู้อยู่ในประเทศไทยตามเกณฑ์ภาษี (รวมอย่างน้อย 180 วันในปีภาษี)</label>
                    <label class="check-label" for="disability_confirmed">
                        <input type="checkbox" name="disability_confirmed" id="disability_confirmed" value="1" @checked($value('disability_confirmed'))> กรณีใช้สิทธิคนพิการ: มีบัตร/หลักฐานรับรองความพิการตามเกณฑ์กรมสรรพากร และอายุไม่เกิน 65 ปี</label>
                </div>
            </fieldset>
            <fieldset>
                <legend>สิทธิอุปการะคนพิการ / ทุพพลภาพ (แยกจากสิทธิของตนเอง)</legend>
                @foreach(['disabled_family_count' => 'จำนวนบุคคลในครอบครัวที่มีสิทธิอุปการะ', 'disabled_other_count' => 'จำนวนบุคคลอื่นที่มีสิทธิอุปการะ (ไม่เกิน 1 คน)'] as $name => $label)
                    <label for="{{ $name }}">{{ $label }}</label>
                    <input type="number" name="{{ $name }}" id="{{ $name }}" min="0" step="1" value="{{ $value($name) }}">
                @endforeach
                <p class="field-help">ลดหย่อนคนละ 60,000 บาท ครอบครัว ได้แก่ บิดามารดา คู่สมรส บุตรชอบด้วยกฎหมาย/บุญธรรม และบิดามารดาหรือบุตรชอบด้วยกฎหมายของคู่สมรส ผู้รับอุปการะต้องมีเงินได้ไม่เกิน 30,000 บาทตามเกณฑ์ มีหลักฐานคนพิการหรือใบรับรองทุพพลภาพอย่างน้อย 180 วัน พร้อมหลักฐานผู้ดูแล/หนังสือยินยอม และไม่ใช้สิทธิซ้ำกับผู้อื่น</p>
                <label class="check-label" for="care_confirmed"><input type="checkbox" name="care_confirmed" id="care_confirmed" value="1" @checked($value('care_confirmed'))> ยืนยันว่าผู้รับอุปการะทุกคนเข้าเงื่อนไขและมีหลักฐานครบ</label>
            </fieldset>
            <hr>
            <div class="income">
                <h3>2. เงินได้พึงประเมินทั้งปี</h3>
                @foreach(range(1, 4) as $group)
                    @if($incomeTypes->has('40_'.$group))
                    <div class="income_g{{ $group }}">
                        <label for="income_g{{ $group }}">{{ $group }}. {{ $incomeTypes['40_'.$group]->name }} ม.40({{ $group }})</label>
                        <input type="number" name="income_g{{ $group }}" id="income_g{{ $group }}" min="0" step="0.01" value="{{ $value('income_g'.$group) }}">
                    </div>
                    @endif
                @endforeach
                @if($incomeTypes->has('40_5'))
                <div class="income_g5">
                    <label for="income_g5_type">5. ประเภททรัพย์สินให้เช่า ม.40(5)</label>
                    <select name="income_g5_type" id="income_g5_type">
                        @foreach(['none' => 'ไม่มีรายได้ในส่วนนี้', 'building' => 'บ้าน / อาคาร / แพ (30%)', 'agriculture_land' => 'ที่ดินใช้ในการเกษตร (20%)', 'other_land' => 'ที่ดินอื่น (15%)', 'vehicle' => 'ยานพาหนะ (30%)', 'other_property' => 'ทรัพย์สินอื่น (10%)'] as $key => $label)
                            <option value="{{ $key }}" @selected($value('income_g5_type', 'none') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="field-help">กรอกหนึ่งประเภทต่อการคำนวณ ไม่รวมการให้เช่าช่วงหรือผิดสัญญาเช่าซื้อ</p>
                    <div id="income_g5_amount">
                        <label for="income_g5">รายได้ค่าเช่าทั้งปี</label>
                        <input type="number" name="income_g5" id="income_g5" min="0" step="0.01" value="{{ $value('income_g5') }}">
                        <label for="income_g5_method">วิธีหักค่าใช้จ่าย</label>
                        <select name="income_g5_method" id="income_g5_method">
                            <option value="flat" @selected($value('income_g5_method', 'flat') === 'flat')>หักเหมาตามประเภททรัพย์สิน</option>
                            <option value="actual" @selected($value('income_g5_method', 'flat') === 'actual')>ค่าใช้จ่ายจริงที่จำเป็นและสมควร มีหลักฐาน</option>
                        </select>
                        <div id="expense_g5_fields">
                            <label for="expense_g5">ค่าใช้จ่ายจริงค่าเช่า</label>
                            <input type="number" name="expense_g5" id="expense_g5" min="0" step="0.01" value="{{ $value('expense_g5') }}">
                        </div>
                    </div>
                </div>
                @endif
                @if($incomeTypes->has('40_6'))
                <div class="income_g6">
                    <label for="income_g6_type">6. ประเภทวิชาชีพอิสระ ม.40(6)</label>
                    <select name="income_g6_type" id="income_g6_type">
                        @foreach(['none' => 'ไม่มีรายได้ในส่วนนี้', 'medical' => 'ประกอบโรคศิลปะ / แพทย์ (60%)', 'other' => 'กฎหมาย บัญชี วิศวกรรม สถาปัตยกรรม ประณีตศิลปกรรม (30%)'] as $key => $label)
                            <option value="{{ $key }}" @selected($value('income_g6_type', 'none') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div id="income_g6_amount">
                        <label for="income_g6">จำนวนเงินรายได้ ม.40(6)</label>
                        <input type="number" name="income_g6" id="income_g6" min="0" step="0.01" value="{{ $value('income_g6') }}">
                    </div>
                </div>
                @endif
                @if($incomeTypes->has('40_7'))
                <div class="income_g7">
                    <label for="income_g7">7. รับเหมาก่อสร้างที่ผู้รับเหมาจัดหาสัมภาระสำคัญ ม.40(7) (หักเหมา 60%)</label>
                    <input type="number" name="income_g7" id="income_g7" min="0" step="0.01" value="{{ $value('income_g7') }}">
                </div>
                @endif
                @if($incomeTypes->has('40_8'))
                <div class="income_g8">
                    <label for="income_g8_type">8. ประเภทกิจการ ม.40(8)</label>
                    <select name="income_g8_type" id="income_g8_type">
                        @foreach(['none' => 'ไม่มีรายได้ในส่วนนี้', 'resale' => 'ซื้อสินค้ามาขาย โดยไม่ได้เป็นผู้ผลิต (เหมา 60%)', 'restaurant' => 'กิจการปรุงอาหารหรือเครื่องดื่มจำหน่าย (เหมา 60%)', 'other' => 'กิจการอื่น (ใช้ค่าใช้จ่ายจริง)'] as $key => $label)
                            <option value="{{ $key }}" @selected($value('income_g8_type', 'none') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="field-help">กรอกหนึ่งกิจการต่อการคำนวณ ไม่รวมขายอสังหาริมทรัพย์ นักแสดง หรือเงินได้ที่มีกฎคำนวณเฉพาะ</p>
                    <div id="income_g8_amount">
                        <label for="income_g8">รายได้ธุรกิจทั้งปี</label>
                        <input type="number" name="income_g8" id="income_g8" min="0" step="0.01" value="{{ $value('income_g8') }}">
                        <label for="income_g8_method">วิธีหักค่าใช้จ่าย</label>
                        <select name="income_g8_method" id="income_g8_method">
                            <option value="actual" @selected($value('income_g8_method', 'actual') === 'actual')>ค่าใช้จ่ายจริงที่จำเป็นและสมควร มีหลักฐาน</option>
                            <option value="flat" @selected($value('income_g8_method', 'actual') === 'flat')>หักเหมา 60% เฉพาะกิจการที่รองรับ</option>
                        </select>
                        <div id="expense_g8_fields">
                            <label for="expense_g8">ค่าใช้จ่ายจริงธุรกิจ</label>
                            <input type="number" name="expense_g8" id="expense_g8" min="0" step="0.01" value="{{ $value('expense_g8') }}">
                        </div>
                        <label class="check-label" for="business_confirmed"><input type="checkbox" name="business_confirmed" id="business_confirmed" value="1" @checked($value('business_confirmed'))> เมื่อหักเหมา ยืนยันว่ารายได้ทั้งหมดตรงกับกิจการที่เลือก</label>
                    </div>
                </div>
                @endif
            </div>
            <hr>
            <div class="deductions">
                <h3>3. ค่าลดหย่อนและภาษีที่ชำระไว้แล้ว</h3>
                <div class="deduct_g1">
                    <label for="deduct_social">เงินสมทบประกันสังคมตามที่จ่ายจริง (สูงสุด <span id="social-cap">{{ number_format($socialCap) }}</span> บาท)</label>
                    <input type="number" name="deduct_social" id="deduct_social" min="0" max="{{ $socialCap }}" step="0.01" value="{{ $value('deduct_social') }}">
                    <p class="field-help">เพดาน ม.33 เมื่อส่งเต็มปี: 2568 = 9,000 บาท; 2569 = 10,500 บาท ผู้ประกันตนประเภทอื่นกรอกเฉพาะยอดที่มีสิทธิลดหย่อนตามจริง</p>
                </div>
                @foreach(['deduct_life' => 'ประกันชีวิตที่เข้าเกณฑ์ (สูงสุด 100,000 บาท)', 'deduct_health' => 'ประกันสุขภาพตนเองที่เข้าเกณฑ์ (สูงสุด 25,000 บาท; รวมชีวิตไม่เกิน 100,000 บาท)', 'deduct_donation' => 'เงินบริจาคทั่วไปที่มีสิทธิ (ไม่เกิน 10% ของเงินได้หลังค่าใช้จ่ายและลดหย่อน)', 'withholding_tax' => 'ภาษีหัก ณ ที่จ่ายทั้งปีตามหนังสือรับรอง', 'half_year_tax' => 'ภาษีที่ชำระตาม ภ.ง.ด.94 ของปีภาษีนี้ (ไม่รวมเบี้ยปรับ/เงินเพิ่ม)'] as $name => $label)
                    <div class="deduct_g2">
                        <label for="{{ $name }}">{{ $label }}</label>
                        <input type="number" name="{{ $name }}" id="{{ $name }}" min="0" step="0.01" value="{{ $value($name) }}">
                    </div>
                @endforeach
            </div>
            <p class="field-help">ยังไม่รวมสิทธิอื่น เช่น กองทุน บิดามารดา ดอกเบี้ยบ้าน เครดิตเงินปันผล และเงินได้ต่างประเทศ อย่านำเงินได้ที่เลือกเสียภาษีสุดท้ายแยกแล้ว หรือภาษีของเงินได้นั้น มากรอกซ้ำ</p>
            <button type="submit" name="action" value="calculate">คำนวณภาษี</button>
        </form>
        <div class="net-income" aria-live="polite">
            <h3>ผลคำนวณภาษีทั้งปี</h3>
            @if(isset($netIncome, $taxPayable))
                <p>ปีภาษี {{ $form['tax_year'] }}</p>
                <div class="result-box">
                    <article class="result-card">
                        <span class="result-label">เงินได้สุทธิ</span>
                        <strong class="amount">{{ number_format($netIncome, 2) }}</strong>
                        <small>บาท</small>
                    </article>
                    <article class="result-card">
                        <span class="result-label">ภาษีทั้งปีก่อนหักยอดชำระไว้</span>
                        <strong class="taxpay">{{ number_format($taxPayable, 2) }}</strong>
                        <small>บาท</small>
                    </article>
                    <article class="result-card">
                        <span class="result-label">ภาษีหัก ณ ที่จ่าย</span>
                        <strong class="paid-amount">{{ number_format($withholdingTax, 2) }}</strong>
                        <small>บาท</small>
                    </article>
                    @if($refund > 0)
                        <article class="result-card refund-card">
                            <span class="result-label">ยอดขอคืนโดยประมาณ</span>
                            <strong class="amount">{{ number_format($refund, 2) }}</strong>
                            <small>บาท</small>
                        </article>
                    @else
                        <article class="result-card payment-card">
                            <span class="result-label">ยอดชำระเพิ่ม</span>
                            <strong class="taxpay">{{ number_format($additionalTax, 2) }}</strong>
                            <small>บาท</small>
                        </article>
                    @endif
                </div>
                <details class="calculation-details">
                    <summary>ดูรายละเอียดการคำนวณ</summary>
                    <div class="calculation-detail-grid">
                        <p><span>เงินได้ที่ยกเว้น</span><strong>{{ number_format($exemptIncome, 2) }} บาท</strong></p>
                        <p><span>ค่าใช้จ่าย</span><strong>{{ number_format($expense, 2) }} บาท</strong></p>
                        <p><span>ค่าลดหย่อนก่อนบริจาค</span><strong>{{ number_format($deduction, 2) }} บาท</strong></p>
                        <p><span>เงินบริจาคที่ใช้สิทธิ</span><strong>{{ number_format($donation, 2) }} บาท</strong></p>
                        <p><span>ภาษีครึ่งปี</span><strong>{{ number_format($halfYearTax, 2) }} บาท</strong></p>
                        <p><span>ยอดภาษีชำระไว้รวม</span><strong>{{ number_format($taxPaid, 2) }} บาท</strong></p>
                        <p><span>ภาษีขั้นบันได</span><strong>{{ number_format($stepTax, 2) }} บาท</strong></p>
                        <p><span>วิธี 0.5%</span><strong>{{ number_format($minimumTax, 2) }} บาท</strong></p>
                    </div>
                    <p class="minimum-tax-note">วิธี 0.5% ที่คำนวณได้ไม่เกิน 5,000 บาท จะได้รับยกเว้น</p>
                </details>
                @auth
                    <section class="save-history-card">
                        <div class="save-history-icon" aria-hidden="true">✓</div>
                        <div class="save-history-content">
                            <h4>บันทึกผลคำนวณนี้</h4>
                            <p>ตั้งชื่อรายการเพื่อกลับมาดูหรือแก้ไขรายละเอียดภายหลัง</p>
                            <form action="{{ route('tax.save') }}" method="POST" class="history-save-form">
                                @csrf
                                <label for="title" class="sr-only">ชื่อรายการประวัติ</label>
                                <input type="text" name="title" id="title" maxlength="100" placeholder="เช่น คำนวณก่อนยื่น ภ.ง.ด.90">
                                <button type="submit" class="save-history-button">
                                    <span class="save-button-icon" aria-hidden="true">✓</span>
                                    <span>บันทึกผลคำนวณ</span>
                                </button>
                            </form>
                            <small>หากไม่ตั้งชื่อ ระบบจะสร้างชื่อรายการให้อัตโนมัติ</small>
                        </div>
                    </section>
                @else
                    <section class="save-history-card guest-save-card">
                        <div class="save-history-icon" aria-hidden="true">↗</div>
                        <div class="save-history-content">
                            <h4>ต้องการเก็บผลคำนวณนี้ไว้หรือไม่?</h4>
                            <p>เข้าสู่ระบบเพื่อบันทึกผลและกลับมาดูประวัติได้ภายหลัง</p>
                            <a href="{{ route('login') }}" class="login-to-save">เข้าสู่ระบบเพื่อบันทึก</a>
                        </div>
                    </section>
                @endauth
            @else
                <p>กรอกข้อมูลแล้วกดคำนวณภาษี</p>
            @endif
        </div>
        <p class="field-help">หลักเกณฑ์อ้างอิง <a href="https://www.rd.go.th/556.html" target="_blank" rel="noopener">กรมสรรพากร: ค่าใช้จ่าย</a> และ <a href="https://www.rd.go.th/555.html" target="_blank" rel="noopener">การคำนวณภาษีสิ้นปี</a></p>
    </main>
    <footer><p>&copy; {{ date('Y') }} PersonalTaxCalculator</p></footer>
    <script src="{{ asset('js/calculate.js') }}" defer></script>
</body>
</html>
