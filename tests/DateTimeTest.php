<?php
use Lucid\Component\Container\Container;

/*
The only difference between a RequestContainer and Container is how booleans are handled,
so this unit test only  Addresses boolean values.
*/
class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    public function testDateTime()
    {
        $container = new Container();
        $container->set('unix_jan1_1970', 1);
        # $container->set('iso8601_jan1_1970', );

        $dt1 = $container->DateTime('unix_jan1_1970');
        $this->assertEquals(get_class($dt1), 'DateTime');
        $this->assertEquals($dt1->format(\DateTime::ISO8601), '1970-01-01T00:00:01+0000');
        $this->assertEquals($dt1->format('U'), 1);

        $container->set('iso8601_jan1_1970', $dt1->format(\DateTime::ISO8601));
        $dt2 = $container->DateTime('iso8601_jan1_1970');
        $this->assertEquals($dt2->format(\DateTime::ISO8601), '1970-01-01T00:00:01+0000');
        $this->assertEquals($dt2->format('U'), 1);

        $container->set('goingToError', 'hello');
        $this->setExpectedException(\Lucid\Component\Container\DateTimeParseException::class);
        $container->DateTime('goingToError');

    }
}