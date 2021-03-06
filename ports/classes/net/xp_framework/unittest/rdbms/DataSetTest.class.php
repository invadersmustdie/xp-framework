<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'rdbms.DriverManager',
    'rdbms.DBObserver',
    'util.Date',
    'util.collections.Vector',
    'lang.types.String',
    'util.DateUtil',
    'rdbms.Statement',
    'net.xp_framework.unittest.rdbms.mock.MockConnection',
    'net.xp_framework.unittest.rdbms.dataset.Job'
  );
  
  /**
   * O/R-mapping API unit test
   *
   * @see      xp://rdbms.DataSet
   * @purpose  TestCase
   */
  class DataSetTest extends TestCase {
    const
      MOCK_CONNECTION_CLASS = 'net.xp_framework.unittest.rdbms.mock.MockConnection',
      IRRELEVANT_NUMBER     = -1;

    /**
     * Mock connection registration
     *
     */  
    #[@beforeClass]
    public static function registerMockConnection() {
      DriverManager::register('mock', XPClass::forName(self::MOCK_CONNECTION_CLASS));
    }
    
    /**
     * Setup method
     *
     */
    public function setUp() {
      Job::getPeer()->setConnection(DriverManager::getConnection('mock://mock/JOBS?autoconnect=1'));
    }
    
    /**
     * Helper methods
     *
     * @return  net.xp_framework.unittest.rdbms.mock.MockConnection
     */
    protected function getConnection() {
      return Job::getPeer()->getConnection();
    }
    
    /**
     * Helper method
     *
     * @param   net.xp_framework.unittest.rdbms.mock.MockResultSet r
     */
    protected function setResults($r) {
      $this->getConnection()->setResultSet($r);
    }
    
    /**
     * Tests the getPeer() method
     *
     */
    #[@test]
    public function peerObject() {
      $peer= Job::getPeer();
      $this->assertClass($peer, 'rdbms.Peer');
      $this->assertEquals('job', strtolower($peer->identifier));
      $this->assertEquals('jobs', $peer->connection);
      $this->assertEquals('JOBS.job', $peer->table);
      $this->assertEquals('job_id', $peer->identity);
      $this->assertEquals(
        array('job_id'), 
        $peer->primary
      );
      $this->assertEquals(
        array('job_id', 'title', 'valid_from', 'expire_at'),
        array_keys($peer->types)
      );
    }
    
    /**
     * Tests the getByJob_id() method
     *
     */
    #[@test]
    public function getByJob_id() {
      $now= Date::now();
      $this->setResults(new MockResultSet(array(
        0 => array(   // First row
          'job_id'      => 1,
          'title'       => 'Unit tester',
          'valid_from'  => $now,
          'expire_at'   => NULL
        )
      )));
      $job= Job::getByJob_id(1);
      $this->assertClass($job, 'net.xp_framework.unittest.rdbms.dataset.Job');
      $this->assertEquals(1, $job->getJob_id());
      $this->assertEquals('Unit tester', $job->getTitle());
      $this->assertEquals($now, $job->getValid_from());
      $this->assertNull($job->getExpire_at());
    }
    
    /**
     * Tests the isNew() method when creating a job object by means of new()
     *
     */
    #[@test]
    public function newObject() {
      $j= new Job();
      $this->assertTrue($j->isNew());
    }

    /**
     * Tests the isNew() method when fetching the object by getByJob_id()
     *
     */
    #[@test]
    public function existingObject() {
      $this->setResults(new MockResultSet(array(
        0 => array(   // First row
          'job_id'      => 1,
          'title'       => 'Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        )
      )));
      
      $job= Job::getByJob_id(1);
      $this->assertNotEquals(NULL, $job);
      $this->assertFalse($job->isNew());
    }

    /**
     * Tests the isNew() method after saving an object
     *
     */
    #[@test]
    public function noLongerNewAfterSave() {
      $j= new Job();
      $j->setTitle('New job');
      $j->setValid_from(Date::now());
      $j->setExpire_at(NULL);
      
      $this->assertTrue($j->isNew());
      $j->save();
      $this->assertFalse($j->isNew());
    }

    /**
     * Tests that getByJob_id() method returns NULL if nothing is found
     *
     */
    #[@test]
    public function noResultsDuringGetByJob_id() {
      $this->setResults(new MockResultSet());
      $this->assertNull(Job::getByJob_id(self::IRRELEVANT_NUMBER));
    }

    /**
     * Tests that getByJob_id() method will throw an exception if the SQL
     * query fails
     *
     */
    #[@test, @expect('rdbms.SQLException')]
    public function failedQueryInGetByJob_id() {
      $mock= $this->getConnection();
      $mock->makeQueryFail(1, 'Select failed');

      Job::getByJob_id(self::IRRELEVANT_NUMBER);
    }

    /**
     * Tests that the insert() method will return the identity value
     *
     * @see     xp://rdbms.DataSet#insert
     */
    #[@test]
    public function insertReturnsIdentity() {
      $mock= $this->getConnection();
      $mock->setIdentityValue(14121977);

      $j= new Job();
      $j->setTitle('New job');
      $j->setValid_from(Date::now());
      $j->setExpire_at(NULL);

      $id= $j->insert();
      $this->assertEquals(14121977, $id);
    }
    
    /**
     * Tests that the save() method will return the identity value
     *
     * @see     xp://rdbms.DataSet#insert
     */
    #[@test]
    public function saveReturnsIdentityForInserts() {
      $mock= $this->getConnection();
      $mock->setIdentityValue(14121977);

      $j= new Job();
      $j->setTitle('New job');
      $j->setValid_from(Date::now());
      $j->setExpire_at(NULL);

      $id= $j->save();
      $this->assertEquals(14121977, $id);
    }

    /**
     * Tests that the save() method will return the identity value
     *
     * @see     xp://rdbms.DataSet#insert
     */
    #[@test]
    public function saveReturnsIdentityForUpdates() {
      $this->setResults(new MockResultSet(array(
        0 => array(   // First row
          'job_id'      => 1,
          'title'       => 'Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        )
      )));
      
      $job= Job::getByJob_id(1);
      $this->assertNotEquals(NULL, $job);
      $id= $job->save();
      $this->assertEquals(1, $id);
    }
    
    /**
     * Tests that the insert() method will set the identity field's value
     * and that it is set to its initial value before.
     *
     * @see     xp://rdbms.DataSet#insert
     */
    #[@test]
    public function identityFieldIsSet() {
      $mock= $this->getConnection();
      $mock->setIdentityValue(14121977);

      $j= new Job();
      $j->setTitle('New job');
      $j->setValid_from(Date::now());
      $j->setExpire_at(NULL);

      $this->assertEquals(0, $j->getJob_id());

      $j->insert();
      $this->assertEquals(14121977, $j->getJob_id());
    }
    
    /**
     * Tests that the insert() method will throw an exception in case the
     * SQL query fails
     *
     * @see     xp://rdbms.DataSet#insert
     */
    #[@test, @expect('rdbms.SQLException')]
    public function failedQueryInInsert() {
      $mock= $this->getConnection();
      $mock->makeQueryFail(1205, 'Deadlock');

      $j= new Job();
      $j->setTitle('New job');
      $j->setValid_from(Date::now());
      $j->setExpire_at(NULL);

      $j->insert();
    }
    
    /**
     * Tests that the doSelect() will return an array of objects
     *
     */
    #[@test]
    public function oneResultForDoSelect() {
      $this->setResults(new MockResultSet(array(
        0 => array(
          'job_id'      => 1,
          'title'       => 'Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        )
      )));
    
      $peer= Job::getPeer();
      $jobs= $peer->doSelect(new Criteria(array('title', 'Unit tester', EQUAL)));

      $this->assertArray($jobs);
      $this->assertEquals(1, sizeof($jobs));
      $this->assertClass($jobs[0], 'net.xp_framework.unittest.rdbms.dataset.Job');
    }

    /**
     * Tests that the doSelect() will return an empty array if nothing is found
     *
     */
    #[@test]
    public function noResultForDoSelect() {
      $this->setResults(new MockResultSet());
    
      $peer= Job::getPeer();
      $jobs= $peer->doSelect(new Criteria(array('job_id', self::IRRELEVANT_NUMBER, EQUAL)));

      $this->assertArray($jobs);
      $this->assertEquals(0, sizeof($jobs));
    }

    /**
     * Tests that the doSelect() will return an array of objects
     *
     */
    #[@test]
    public function multipleResultForDoSelect() {
      $this->setResults(new MockResultSet(array(
        0 => array(
          'job_id'      => 1,
          'title'       => 'Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        ),
        1 => array(
          'job_id'      => 9,
          'title'       => 'PHP programmer',
          'valid_from'  => Date::now(),
          'expire_at'   => DateUtil::addDays(Date::now(), 7)
        )
      )));
    
      $peer= Job::getPeer();
      $jobs= $peer->doSelect(new Criteria(array('job_id', 10, LESS_THAN)));

      $this->assertArray($jobs);
      $this->assertEquals(2, sizeof($jobs));
      $this->assertClass($jobs[0], 'net.xp_framework.unittest.rdbms.dataset.Job');
      $this->assertEquals(1, $jobs[0]->getJob_id());
      $this->assertClass($jobs[1], 'net.xp_framework.unittest.rdbms.dataset.Job');
      $this->assertEquals(9, $jobs[1]->getJob_id());
    }
    
    /**
     * Tests the iteratorFor() method with criteria
     *
     */
    #[@test]
    public function iterateOverCriteria() {
      $this->setResults(new MockResultSet(array(
        0 => array(
          'job_id'      => 654,
          'title'       => 'Java Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        ),
        1 => array(
          'job_id'      => 329,
          'title'       => 'C# programmer',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        )
      )));

      $peer= Job::getPeer();
      $iterator= $peer->iteratorFor(new Criteria(array('expire_at', NULL, EQUAL)));

      $this->assertClass($iterator, 'rdbms.ResultIterator');
      
      // Make sure hasNext() does not forward the resultset pointer
      $this->assertTrue($iterator->hasNext());
      $this->assertTrue($iterator->hasNext());
      $this->assertTrue($iterator->hasNext());
      
      $job= $iterator->next();
      $this->assertClass($job, 'net.xp_framework.unittest.rdbms.dataset.Job');
      $this->assertEquals(654, $job->getJob_id());

      $this->assertTrue($iterator->hasNext());

      $job= $iterator->next();
      $this->assertClass($job, 'net.xp_framework.unittest.rdbms.dataset.Job');
      $this->assertEquals(329, $job->getJob_id());

      $this->assertFalse($iterator->hasNext());
    }

    /**
     * Tests that ResultIterator::next() can be called without previously having
     * called hasMext()
     *
     */
    #[@test]
    public function nextCallWithoutHasNext() {
      $this->setResults(new MockResultSet(array(
        0 => array(
          'job_id'      => 654,
          'title'       => 'Java Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        ),
        1 => array(
          'job_id'      => 329,
          'title'       => 'C# programmer',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        )
      )));

      $peer= Job::getPeer();
      $iterator= $peer->iteratorFor(new Criteria(array('expire_at', NULL, EQUAL)));

      $job= $iterator->next();
      $this->assertClass($job, 'net.xp_framework.unittest.rdbms.dataset.Job');
      $this->assertEquals(654, $job->getJob_id());

      $this->assertTrue($iterator->hasNext());
    }

    /**
     * Tests that ResultIterator::next() will throw an exception in case it
     * is called on an empty resultset.
     *
     */
    #[@test, @expect('util.NoSuchElementException')]
    public function nextCallOnEmptyResultSet() {
      $this->setResults(new MockResultSet());
      $peer= Job::getPeer();
      $iterator= $peer->iteratorFor(new Criteria(array('expire_at', NULL, EQUAL)));
      $iterator->next();
    }

    /**
     * Tests that ResultIterator::next() will throw an exception in case it
     * has iterated past the end of a resultset.
     *
     */
    #[@test, @expect('util.NoSuchElementException')]
    public function nextCallPastEndOfResultSet() {
      $this->setResults(new MockResultSet(array(
        0 => array(
          'job_id'      => 654,
          'title'       => 'Java Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        )
      )));

      $peer= Job::getPeer();
      $iterator= $peer->iteratorFor(new Criteria(array('expire_at', NULL, EQUAL)));
      $iterator->next();
      $iterator->next();
    }
    
    /**
     * Tests the iteratorFor() method with statement
     *
     */
    #[@test]
    public function iterateOverStatement() {
      $this->setResults(new MockResultSet(array(
        0 => array(
          'job_id'      => 654,
          'title'       => 'Java Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        )
      )));

      $peer= Job::getPeer();
      $iterator= $peer->iteratorFor(new Statement('select object(j) from job j where 1 = 1'));
      $this->assertClass($iterator, 'rdbms.ResultIterator');

      $this->assertTrue($iterator->hasNext());

      $job= $iterator->next();
      $this->assertClass($job, 'net.xp_framework.unittest.rdbms.dataset.Job');
      $this->assertEquals(654, $job->getJob_id());
      $this->assertEquals('Java Unit tester', $job->getTitle());

      $this->assertFalse($iterator->hasNext());
    }

    /**
     * Tests that update doesn't do anything when the object is unchanged
     *
     */
    #[@test]
    public function updateUnchangedObject() {

      // First, retrieve an object
      $this->setResults(new MockResultSet(array(
        0 => array(
          'job_id'      => 654,
          'title'       => 'Java Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        )
      )));
      $job= Job::getByJob_id(1);
      $this->assertNotEquals(NULL, $job);

      // Second, update the job. Make the next query fail on this 
      // connection to ensure that nothing is actually done.
      $mock= $this->getConnection();
      $mock->makeQueryFail(1326, 'Syntax error');
      $job->update();

      // Make next query return empty results (not fail)
      $this->setResults(new MockResultSet());
    }

    /**
     * Tests column
     *
     */
    #[@test]
    public function column() {
      $c= Job::column('job_id');
      $this->assertClass($c, 'rdbms.Column');
      $this->assertEquals('job_id', $c->getName());
    }

    /**
     * Tests column exeption
     *
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function nonExistantColumn() {
      Job::column('non_existant');
    }

    /**
     * Tests column of relatives
     *
     */
    #[@test]
    public function relativeColumn() {
      $this->assertClass(Job::column('PersonJob->person_id'), 'rdbms.Column');
    }

    /**
     * Tests column of relatives exeption
     *
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function nonExistantRelativeColumn() {
      Job::column('PersonJob->non_existant');
    }

    /**
     * Tests column of relatives
     *
     */
    #[@test]
    public function farRelativeColumn() {
      $this->assertClass(Job::column('PersonJob->Department->department_id'), 'rdbms.Column');
    }

    /**
     * Tests column of relatives exeption
     *
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function nonExistantfarRelativeColumn() {
      Job::column('PersonJob->Department->non_existant');
    }

    /**
     * Tests relation exeption
     *
     */
    #[@test, @expect('lang.IllegalArgumentException')]
    public function nonExistantRelative() {
      Job::column('NonExistant->person_id');
    }


    /**
     * Tests doUpdate()
     *
     */
    #[@test]
    public function doUpdate() {
      $this->setResults(new MockResultSet(array(
        0 => array(
          'job_id'      => 654,
          'title'       => 'Java Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        )
      )));
      $job= Job::getByJob_id(654);
      $this->assertNotEquals(NULL, $job);
      $job->setTitle('PHP Unit tester');
      $job->doUpdate(new Criteria(array('job_id', $job->getJob_id(), EQUAL)));
    }

    /**
     * Tests doDelete()
     *
     */
    #[@test]
    public function doDelete() {
      $this->setResults(new MockResultSet(array(
        0 => array(
          'job_id'      => 654,
          'title'       => 'Java Unit tester',
          'valid_from'  => Date::now(),
          'expire_at'   => NULL
        )
      )));
      $job= Job::getByJob_id(654);
      $this->assertNotEquals(NULL, $job);
      $job->doDelete(new Criteria(array('job_id', $job->getJob_id(), EQUAL)));
    }

    /**
     * Tests percent signs don't get messed up during dataset processing
     * Round-trip test.
     *
     */
    #[@test]
    public function percentSign() {
      $observer= $this->getConnection()->addObserver(newinstance('rdbms.DBObserver', array(create('new Vector<lang.types.String>')), '{
        public $statements;
        public function __construct($statements) {
          $this->statements= $statements;
        }
        public static function instanceFor($arg) { }
        public function update($observable, $event= NULL) {
          if ($event instanceof DBEvent && "query" == $event->getName()) {
            $this->statements[]= new String($event->getArgument());
          }
        }
      }'));
      $j= new Job();
      $j->setTitle('Percent%20Sign');
      $j->insert();
      
      $this->assertEquals(
        new String('insert into JOBS.job (title) values ("Percent%20Sign")'),
        $observer->statements[0]
      );
    }

    /**
     * Test the max parameter with Peer::doSelect
     *
     */
    #[@test]
    public function testDoSelectMax() {
      for ($i= 0; $i < 4; $i++) {
        $this->setResults(new MockResultSet(array(
          0 => array(
            'job_id'      => 654,
            'title'       => 'Java Unit tester',
            'valid_from'  => Date::now(),
            'expire_at'   => NULL
          ),
          1 => array(
            'job_id'      => 655,
            'title'       => 'Java Unit tester 1',
            'valid_from'  => Date::now(),
            'expire_at'   => NULL
          ),
          2 => array(
            'job_id'      => 656,
            'title'       => 'Java Unit tester 2',
            'valid_from'  => Date::now(),
            'expire_at'   => NULL
          ),
          3 => array(
            'job_id'      => 657,
            'title'       => 'Java Unit tester 3',
            'valid_from'  => Date::now(),
            'expire_at'   => NULL
          ),
        )));
        $this->assertEquals($i ? $i : 4, count(Job::getPeer()->doSelect(new Criteria(), $i)));
      }
    }
  }
?>
