<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) 2016 overtrue <i@overtrue.me>
 */

use Overtrue\Pinyin\DictLoaderInterface;
use Overtrue\Pinyin\Pinyin;

class PinyinTest extends PHPUnit_Framework_TestCase
{
    public function testConvert()
    {
        $pinyin = new Pinyin();

        $this->assertSame(array('nin', 'hao'), $pinyin->convert('您好!'));
        $this->assertSame(array('nin', 'hao'), $pinyin->convert('您好!', Pinyin::NONE));
        $this->assertSame(array('nin2', 'hao3'), $pinyin->convert('您好!', Pinyin::ASCII));
        $this->assertSame(array('nín', 'hǎo'), $pinyin->convert('您好!', Pinyin::UNICODE));
    }

    public function testPermlink()
    {
        $pinyin = new Pinyin();

        $this->assertSame('dai-zhe-xi-wang-qu-lv-xing', $pinyin->permlink('带着希望去旅行'));
        $this->assertSame('dai_zhe_xi_wang_qu_lv_xing', $pinyin->permlink('带着希望去旅行', '_'));
        $this->assertSame('dai.zhe.xi.wang.qu.lv.xing', $pinyin->permlink('带着希望去旅行', '.'));
        $this->assertSame('daizhexiwangqulvxing', $pinyin->permlink('带着希望去旅行', ''));

        $this->setExpectedException('InvalidArgumentException', "Delimiter must be one of: '_', '-', '', '.'.");

        $this->assertSame('daizhexiwangqulvxing', $pinyin->permlink('带着希望去旅行', '='));
    }

    public function testAbbr()
    {
        $pinyin = new Pinyin();

        $this->assertSame('dzxwqlx', $pinyin->abbr('带着希望去旅行'));
        $this->assertSame('d z x w q l x', $pinyin->abbr('带着希望去旅行', ' '));
        $this->assertSame('d*z*x*w*q*l*x', $pinyin->abbr('带着希望去旅行', '*'));
        $this->assertSame('d--z--x--w--q--l--x', $pinyin->abbr('带着希望去旅行', '--'));

        // issue #27
        $this->assertEquals('dldnr', $pinyin->abbr('独立的女人'));
        $this->assertEquals('n', $pinyin->abbr('女'));
        $this->assertEquals('nr', $pinyin->abbr('女人'));
    }

    public function testSentence()
    {
        $pinyin = new Pinyin();

        $this->assertSame('dai zhe xi wang qu lv xing, bi dao da zhong dian geng mei hao!', $pinyin->sentence('带着希望去旅行，比到达终点更美好！'));

        $this->assertSame('dai zhe xi 123 wang qu good lv boy2 xing!.', $pinyin->sentence('带^着&*希123望去good旅boy2行！.'));
        $this->assertSame('dai zhe xi 123 wang.. qu good lv boy2 xing!.', $pinyin->sentence('--带^着&*希123望.。去good旅boy2行！.'));
    }

    public function testLoaderSetter()
    {
        $pinyin = new Pinyin();

        $loader = new MockLoader();

        $pinyin->setLoader($loader);

        $this->assertSame($loader, $pinyin->getLoader());
        $this->assertSame('foo bar', $pinyin->sentence('你好'));
    }

    // test special words
    public function testSpecialWords()
    {
        $pinyin = new Pinyin();

        $this->assertEquals('hello, world!', $pinyin->sentence('hello, world!', true));
        $this->assertEquals('DNA jiàn dìng', $pinyin->sentence('DNA鉴定', true));
        $this->assertEquals('21 sān tǐ zōng hé zhèng', $pinyin->sentence('21三体综合症', true));
        $this->assertEquals('C pán', $pinyin->sentence('C盘', true));
        $this->assertEquals('G diǎn', $pinyin->sentence('G点', true));
        $this->assertEquals('zhōng dù xìng fèi shuǐ zhǒng', $pinyin->sentence('中度性肺水肿', true));
    }

    // test Polyphone
    public function testPolyphone()
    {
        $pinyin = new Pinyin();

        // 了
        $this->assertEquals(array('liǎo', 'rán'), $pinyin->convert('了然', Pinyin::UNICODE));
        $this->assertEquals(array('lái', 'le'), $pinyin->convert('来了', Pinyin::UNICODE));

        // 还
        $this->assertEquals(array('hái', 'yǒu'), $pinyin->convert('还有', Pinyin::UNICODE));
        $this->assertEquals(array('jiāo', 'huán'), $pinyin->convert('交还', Pinyin::UNICODE));

        // 什
        $this->assertEquals(array('shén', 'me'), $pinyin->convert('什么', Pinyin::UNICODE));
        $this->assertEquals(array('shí', 'jǐn'), $pinyin->convert('什锦', Pinyin::UNICODE));

        // 便
        $this->assertEquals(array('biàn', 'dāng'), $pinyin->convert('便当', Pinyin::UNICODE));
        $this->assertEquals(array('pián', 'yí'), $pinyin->convert('便宜', Pinyin::UNICODE));

        // 剥
        $this->assertEquals(array('bō', 'xuē'), $pinyin->convert('剥削', Pinyin::UNICODE));
        $this->assertEquals(array('bāo', 'pí', 'qì'), $pinyin->convert('剥皮器', Pinyin::UNICODE));

        // 不
        $this->assertEquals(array('péi', 'bú', 'shì'), $pinyin->convert('赔不是', Pinyin::UNICODE));
        $this->assertEquals(array('pǎo', 'le', 'hé', 'shàng', 'pǎo', 'bù', 'liǎo', 'miào'), $pinyin->convert('跑了和尚，跑不了庙', Pinyin::UNICODE));

        // 降
        $this->assertEquals(array('jiàng', 'wēn'), $pinyin->convert('降温', Pinyin::UNICODE));
        $this->assertEquals(array('tóu', 'xiáng'), $pinyin->convert('投降', Pinyin::UNICODE));

        // 都
        $this->assertEquals(array('shǒu', 'dū'), $pinyin->convert('首都', Pinyin::UNICODE));
        $this->assertEquals(array('dōu', 'shén', 'me', 'nián', 'dài', 'le'), $pinyin->convert('都什么年代了', Pinyin::UNICODE));

        // 乐
        $this->assertEquals(array('kuài', 'lè'), $pinyin->convert('快乐', Pinyin::UNICODE));
        $this->assertEquals(array('yīn', 'yuè'), $pinyin->convert('音乐', Pinyin::UNICODE));

        // 长
        $this->assertEquals(array('chéng', 'zhǎng'), $pinyin->convert('成长', Pinyin::UNICODE));
        $this->assertEquals(array('cháng', 'jiāng'), $pinyin->convert('长江', Pinyin::UNICODE));

        // 难
        $this->assertEquals(array('nàn', 'mín'), $pinyin->convert('难民', Pinyin::UNICODE));
        $this->assertEquals(array('nán', 'guò'), $pinyin->convert('难过', Pinyin::UNICODE));

        // 厦
        $this->assertEquals(array('dà', 'shà'), $pinyin->convert('大厦', Pinyin::UNICODE));
        $this->assertEquals(array('xià', 'mén'), $pinyin->convert('厦门', Pinyin::UNICODE));

        // 曾
        $this->assertEquals(array('céng', 'jīng'), $pinyin->convert('曾经', Pinyin::UNICODE));
        $this->assertEquals(array('xìng', 'zēng'), $pinyin->convert('姓曾', Pinyin::UNICODE));
    }

    /**
     * 测试字母+数字Bug.
     */
    public function testNumberWithAlpha()
    {
        $pinyin = new Pinyin();

        $this->assertEquals('cè shì R60', $pinyin->sentence('测试R60', true));
        $this->assertEquals('ce shi R60', $pinyin->sentence('测试R60'));
        $this->assertEquals('ce ce5 shi Rr60', $pinyin->sentence('测ce5试Rr60'));
        $this->assertEquals('ce shi R50', $pinyin->sentence('测试R50'));
        $this->assertEquals('ce 3sh4i R50', $pinyin->sentence('测3sh4i R50'));
        $this->assertEquals('ce3sh4i R50', $pinyin->sentence('ce3sh4i R50'));
        $this->assertEquals('33ai4', $pinyin->sentence('33ai4'));
        $this->assertEquals('33ai4 ni', $pinyin->sentence('33ai4你'));
        $this->assertEquals('ai 334 ni', $pinyin->sentence('爱334你'));
        $this->assertEquals('aaaa1234', $pinyin->sentence('aaaa1234'));
        $this->assertEquals('aaaa_1234', $pinyin->sentence('aaaa_1234'));
        $this->assertEquals('ai45 liao wu sheng qu ce shi', $pinyin->sentence('ai45了无生趣测试'));
        $this->assertEquals('java gong cheng shi', $pinyin->sentence('java工程师'));
    }

    /**
     * 测试单个音的字.
     *
     * bug: #19
     * bug: #22
     * bug: #23
     * bug: #24
     * bug: #29
     * bug: #235
     */
    public function testSingleAccent()
    {
        $pinyin = new Pinyin();

        $this->assertEquals('a le tai', $pinyin->sentence('阿勒泰'));
        $this->assertEquals('e er duo si', $pinyin->sentence('鄂尔多斯'));
        $this->assertEquals('zu', $pinyin->sentence('足'));
        $this->assertEquals('feng', $pinyin->sentence('冯'));
        $this->assertEquals('bao hu ping he', $pinyin->sentence('暴虎冯河'));
        $this->assertEquals('hé', $pinyin->sentence('和', Pinyin::UNICODE));
        $this->assertEquals('gěi', $pinyin->sentence('给', Pinyin::UNICODE));
        $this->assertEquals('là', $pinyin->sentence('腊', Pinyin::UNICODE));
        # 28 词库不全
        $this->assertEquals('kun', $pinyin->sentence('堃'));

        #29
        $this->assertEquals('dì', $pinyin->sentence('地', Pinyin::UNICODE));
        $this->assertEquals('zhi yu si di', $pinyin->sentence('置于死地'));

        #35
        $this->assertEquals('ji xiao', $pinyin->sentence('技校'));
        $this->assertEquals('jiao zheng', $pinyin->sentence('校正'));
    }
}

/**
 * Mocker loader.
 */
class MockLoader implements DictLoaderInterface
{
    public function map(Closure $callback)
    {
        $dictionary = array(
                '你好' => "foo\tbar",
            );
        $callback($dictionary);
    }
}
