<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM – Open Source CRM application.
 * Copyright (C) 2014-2025 Yurii Kuznietsov, Taras Machyshyn, Oleksii Avramenko
 * Website: https://www.espocrm.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace tests\unit\Espo\ORM;

use Espo\ORM\Executor\DefaultSqlExecutor;
use Espo\ORM\PDO\PDOProvider;

use PDO;
use PDOStatement;
use PDOException;

class SqlExecutorTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        $this->pdo = $this->createMock(PDO::class);

        $pdoProvider = $this->createMock(PDOProvider::class);
        $pdoProvider
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->pdo);

        $this->sth = $this->getMockBuilder(PDOStatement::class)->disableOriginalConstructor()->getMock();

        $this->executor = new DefaultSqlExecutor($pdoProvider);
    }

    public function testExecute1()
    {
        $sql = "SOME QUERY";

        $this->pdo
            ->expects($this->once())
            ->method('query')
            ->will($this->returnValue($this->sth))
            ->with($sql);

        $sth = $this->executor->execute($sql);

        $this->assertInstanceOf(PDOStatement::class, $sth);
    }

    public function testExecuteException1()
    {
        $sql = "SOME QUERY";

        $e = new PDOException;

        $e->errorInfo = [100, 1001];

        $this->pdo
            ->expects($this->once())
            ->method('query')
            ->will($this->throwException($e));

        try {
            $this->executor->execute($sql);
        } catch (PDOException $e) {

        }
    }

    public function testExecuteException2()
    {
        $sql = "SOME QUERY";

        $e = new PDOException;

        $e->errorInfo = [100, 1001];

        $this->pdo
            ->expects($this->once())
            ->method('query')
            ->will($this->throwException($e));

        try {
            $this->executor->execute($sql, true);
        } catch (PDOException $e) {

        }
    }

    public function testExecuteDeadlock1()
    {
        $sql = "SOME QUERY";

        $e = new PDOException;

        $e->errorInfo = [100, 1001];

        $this->pdo
            ->expects($this->once())
            ->method('query')
            ->will($this->throwException($e));

        try {
            $this->executor->execute($sql);
        } catch (PDOException $e) {

        }
    }

    public function testExecuteDeadlock2()
    {
        $sql = "SOME QUERY";

        $e = new PDOException;

        $e->errorInfo = [40001, 1213];

        $this->pdo
            ->expects($this->exactly(2))
            ->method('query')
            ->will(
                $this->onConsecutiveCalls(
                    $this->throwException($e),
                    $this->returnValue($this->sth)
                )
            );

        $sth = $this->executor->execute($sql, true);

        $this->assertInstanceOf(PDOStatement::class, $sth);
    }
}
