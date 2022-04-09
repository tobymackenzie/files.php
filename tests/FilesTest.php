<?php
namespace TJM\Project\Tests;
use PHPUnit\Framework\TestCase;
use TJM\Files\Files;

class Test extends TestCase{
	protected $tmpPath = __DIR__ . '/tmp';
	public function setUp() :void{
		mkdir($this->tmpPath);
		chdir($this->tmpPath);
	}
	public function tearDown() :void{
		chdir(__DIR__);
		exec('rm -r ' . __DIR__ . '/tmp/*');
		rmdir(__DIR__ . '/tmp');
	}
	public function testSymlinkRelativelySafelySimpleFullPath(){
		file_put_contents('a', 'AAAAA');
		$i = 1;
		Files::symlinkRelativelySafely(__DIR__ . '/tmp/b', __DIR__ . '/tmp/a');
		$this->assertEquals(++$i, count(glob('./*')), "Creating a symlink should increase count of files by one");
		$this->assertEquals('AAAAA', file_get_contents('b'), 'Symlink file should have same contents as target file.');
		file_put_contents('a', 'BBBBB');
		$this->assertEquals('BBBBB', file_get_contents('b'), 'Symlink file should have same contents as target file.');
	}
	public function testSymlinkRelativelySafelySimpleDotPath(){
		file_put_contents('a', 'AAAAA');
		$i = 1;
		Files::symlinkRelativelySafely('./b', './a');
		$this->assertEquals(++$i, count(glob('./*')), "Creating a symlink should increase count of files by one");
		$this->assertEquals('AAAAA', file_get_contents('b'), 'Symlink file should have same contents as target file.');
		file_put_contents('a', 'BBBBB');
		$this->assertEquals('BBBBB', file_get_contents('b'), 'Symlink file should have same contents as target file.');
	}
}
