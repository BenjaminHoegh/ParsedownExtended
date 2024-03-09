<?php

// use PHPUnit\Framework\TestCase;

// class MathTest extends TestCase
// {
//     protected $parsedownExtended;

//     protected function setUp(): void
//     {
//         $this->parsedownExtended = new ParsedownExtended();
//         $this->parsedownExtended->setSafeMode(true); // As we always want to support safe mode
//     }

//     protected function tearDown(): void
//     {
//         unset($this->parsedownExtended);
//     }

//     public function testMathEnabled(): void
//     {
//         $this->parsedownExtended->settings()->set('math', true);
//         $this->assertTrue($this->parsedownExtended->settings()->isEnabled('math'));
//     }

//     public function testMathDisabled(): void
//     {
//         $this->parsedownExtended->settings()->set('math', false);
//         $this->assertFalse($this->parsedownExtended->settings()->isEnabled('math'));
//     }

//     public function testMathDefault(): void
//     {

//         $this->parsedownExtended->settings()->set('math', [
//             'inline' => [
//                 'delimiters' => [
//                     //['left' => '\\(', 'right' => '\\)'],
//                     ['left' => '$', 'right' => '$'],
//                 ],
//             ],
//             'block' => [
//                 'delimiters' => [
//                     ['left' => '$$', 'right' => '$$'],
//                     ['left' => '\\begin{equation}', 'right' => '\\end{equation}'],
//                     ['left' => '\\begin{align}', 'right' => '\\end{align}'],
//                     ['left' => '\\begin{alignat}', 'right' => '\\end{alignat}'],
//                     ['left' => '\\begin{gather}', 'right' => '\\end{gather}'],
//                     ['left' => '\\begin{CD}', 'right' => '\\end{CD}'],
//                     ['left' => '\\[', 'right' => '\\]'],
//                 ],
//             ],
//         ]);
//         $this->assertEquals([
//             'inline' => [
//                 'delimiters' => [
//                     //['left' => '\\(', 'right' => '\\)'],
//                     ['left' => '$', 'right' => '$'],
//                 ],
//             ],
//             'block' => [
//                 'delimiters' => [
//                     ['left' => '$$', 'right' => '$$'],
//                     ['left' => '\\begin{equation}', 'right' => '\\end{equation}'],
//                     ['left' => '\\begin{align}', 'right' => '\\end{align}'],
//                     ['left' => '\\begin{alignat}', 'right' => '\\end{alignat}'],
//                     ['left' => '\\begin{gather}', 'right' => '\\end{gather}'],
//                     ['left' => '\\begin{CD}', 'right' => '\\end{CD}'],
//                     ['left' => '\\[', 'right' => '\\]'],
//                 ],
//             ],
//         ], $this->parsedownExtended->getSetting('math'));
//     }
// }
