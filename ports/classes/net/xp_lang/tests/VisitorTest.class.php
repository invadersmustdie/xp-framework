<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'unittest.TestCase',
    'xp.compiler.Syntax',
    'io.streams.MemoryInputStream',
    'xp.compiler.ast.Visitor'
  );

  /**
   * TestCase
   *
   * @see   xp://xp.compiler.ast.Visitor
   */
  class VisitorTest extends TestCase {
    protected static $visitor;
    
    static function __static() {
      self::$visitor= ClassLoader::defineClass('VisitorTest��Visitor', 'xp.compiler.ast.Visitor', array(), '{
        public $visited= array();
        public function visitOne($node) {
          $this->visited[]= $node;
          return parent::visitOne($node);
        }
      }');
    }
  
    /**
     * Assertion helper
     *
     * @param   xp.compiler.ast.Node[] nodes
     * @param   xp.compiler.ast.Node toVisit
     * @throws  unittest.AssertionFailedError
     */
    protected function assertVisited(array $nodes, xp�compiler�ast�Node $toVisit) {
      $visitor= self::$visitor->newInstance();
      $visitor->visitOne($toVisit);
      $this->assertEquals($nodes, $visitor->visited);
    }

    /**
     * Test visitAnnotation()
     *
     */
    #[@test]
    public function visitAnnotation() {
      $node= new AnnotationNode(array('type' => 'deprecated', 'parameters' => array()));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitAnnotation()
     *
     */
    #[@test]
    public function visitAnnotationWithParameters() {
      $node= new AnnotationNode(array('type' => 'deprecated', 'parameters' => array(
        new StringNode('Use other class instead')
      )));
      $this->assertVisited(array($node, $node->parameters[0]), $node);
    }

    /**
     * Test visitArm()
     *
     */
    #[@test]
    public function visitArm() {
      $node= new ArmNode(
        array(),
        array(new VariableNode('in'), new VariableNode('out')),
        array(new ReturnNode())
      );
      $this->assertVisited(
        array($node, $node->variables[0], $node->variables[1], $node->statements[0]), 
        $node
      );
    }

    /**
     * Test visitArrayAccess()
     *
     */
    #[@test]
    public function visitArrayAccess() {
      $node= new ArrayAccessNode(new VariableNode('a'), new IntegerNode(0));
      $this->assertVisited(array($node, $node->target, $node->offset), $node);
    }

    /**
     * Test visitArrayAccess()
     *
     */
    #[@test]
    public function visitArrayAccessWithoutOffset() {
      $node= new ArrayAccessNode(new VariableNode('a'), NULL);
      $this->assertVisited(array($node, $node->target), $node);
    }

    /**
     * Test visitArray()
     *
     */
    #[@test]
    public function visitArray() {
      $node= new ArrayNode(array('values' => array(new IntegerNode(0), new IntegerNode(1))));
      $this->assertVisited(array($node, $node->values[0], $node->values[1]), $node);
    }

    /**
     * Test visitArray()
     *
     */
    #[@test]
    public function visitEmptyArray() {
      $node= new ArrayNode(array('values' => array()));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitAssignment()
     *
     */
    #[@test]
    public function visitAssignment() {
      $node= new AssignmentNode(array(
        'variable'   => new VariableNode('a'), 
        'expression' => new IntegerNode(0), 
        'op'         => '='
      ));
      $this->assertVisited(array($node, $node->variable, $node->expression), $node);
    }

    /**
     * Test visitBinaryOp()
     *
     */
    #[@test]
    public function visitBinaryOp() {
      $node= new BinaryOpNode(array(
        'lhs' => new VariableNode('a'), 
        'rhs' => new IntegerNode(0), 
        'op'  => '+'
      ));
      $this->assertVisited(array($node, $node->lhs, $node->rhs), $node);
    }

    /**
     * Test visitBoolean()
     *
     */
    #[@test]
    public function visitBoolean() {
      $node= new BooleanNode(TRUE);
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitBooleanOp()
     *
     */
    #[@test]
    public function visitBooleanOp() {
      $node= new BooleanOpNode(array(
        'lhs' => new VariableNode('a'), 
        'rhs' => new IntegerNode(0), 
        'op'  => '&&'
      ));
      $this->assertVisited(array($node, $node->lhs, $node->rhs), $node);
    }

    /**
     * Test visitBreak()
     *
     */
    #[@test]
    public function visitBreak() {
      $node= new BreakNode();
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitCase()
     *
     */
    #[@test]
    public function visitCase() {
      $node= new CaseNode(array(
        'expression' => new IntegerNode(0), 
        'statements' => array(new VariableNode('a'), new BreakNode())
      ));
      $this->assertVisited(array($node, $node->expression, $node->statements[0], $node->statements[1]), $node);
    }

    /**
     * Test visitCast()
     *
     */
    #[@test]
    public function visitCast() {
      $node= new CastNode(array(
        'expression' => new VariableNode('a'), 
        'type'       => new TypeName('int'),
        'check'      => TRUE
      ));
      $this->assertVisited(array($node, $node->expression), $node);
    }

    /**
     * Test visitCatch()
     *
     */
    #[@test]
    public function visitCatch() {
      $node= new CatchNode(array(
        'type'       => new TypeName('int'),
        'variable'   => new VariableNode('a'), 
        'statements' => array(new VariableNode('a'), new BreakNode())
      ));
      $this->assertVisited(array($node, $node->variable, $node->statements[0], $node->statements[1]), $node);
    }

    /**
     * Test visitCatch()
     *
     */
    #[@test]
    public function visitCatchWithEmptyStatements() {
      $node= new CatchNode(array(
        'type'       => new TypeName('int'),
        'variable'   => new VariableNode('a'), 
        'statements' => array()
      ));
      $this->assertVisited(array($node, $node->variable), $node);
    }

    /**
     * Test visitMemberAccess()
     *
     */
    #[@test]
    public function visitMemberAccess() {
      $node= new MemberAccessNode(new VariableNode('this'), 'member'); 
      $this->assertVisited(array($node, $node->target), $node);
    }

    /**
     * Test visitMethodCall()
     *
     */
    #[@test]
    public function visitMethodCall() {
      $node= new MethodCallNode(new VariableNode('this'), 'method', array(new VariableNode('a'))); 
      $this->assertVisited(array($node, $node->target, $node->arguments[0]), $node);
    }

    /**
     * Test visitMethodCall()
     *
     */
    #[@test]
    public function visitMethodCallWithEmptyArgumentList() {
      $node= new MethodCallNode(new VariableNode('this'), 'method', array()); 
      $this->assertVisited(array($node, $node->target), $node);
    }

    /**
     * Test visitMethodCall()
     *
     */
    #[@test]
    public function visitMethodCallWithNullArgumentList() {
      $node= new MethodCallNode(new VariableNode('this'), 'method', NULL); 
      $this->assertVisited(array($node, $node->target), $node);
    }

    /**
     * Test visitInstanceCall()
     *
     */
    #[@test]
    public function visitInstanceCall() {
      $node= new InstanceCallNode(new VariableNode('this'), array(new VariableNode('a'))); 
      $this->assertVisited(array($node, $node->target, $node->arguments[0]), $node);
    }

    /**
     * Test visitInstanceCall()
     *
     */
    #[@test]
    public function visitInstanceCallWithEmptyArgumentList() {
      $node= new InstanceCallNode(new VariableNode('this'), array()); 
      $this->assertVisited(array($node, $node->target), $node);
    }

    /**
     * Test visitInstanceCall()
     *
     */
    #[@test]
    public function visitInstanceCallWithNullArgumentList() {
      $node= new InstanceCallNode(new VariableNode('this'), NULL); 
      $this->assertVisited(array($node, $node->target), $node);
    }

    /**
     * Test visitStaticMemberAccess()
     *
     */
    #[@test]
    public function visitStaticMemberAccess() {
      $node= new StaticMemberAccessNode(new TypeName('self'), 'member'); 
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitStaticMethodCall()
     *
     */
    #[@test]
    public function visitStaticMethodCall() {
      $node= new StaticMethodCallNode(new TypeName('self'), 'method', array(new VariableNode('a'))); 
      $this->assertVisited(array($node, $node->arguments[0]), $node);
    }

    /**
     * Test visitStaticMethodCall()
     *
     */
    #[@test]
    public function visitStaticMethodCallWithEmptyArgumentList() {
      $node= new StaticMethodCallNode(new TypeName('self'), 'method', array()); 
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitStaticMethodCall()
     *
     */
    #[@test]
    public function visitStaticMethodCallWithNullArgumentList() {
      $node= new StaticMethodCallNode(new TypeName('self'), 'method', NULL); 
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitConstantAccess()
     *
     */
    #[@test]
    public function visitConstantAccess() {
      $node= new ConstantAccessNode(new TypeName('self'), 'CONSTANT');
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitClassAccess()
     *
     */
    #[@test]
    public function visitClassAccess() {
      $node= new ClassAccessNode(new TypeName('self'));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitClass()
     *
     */
    #[@test]
    public function visitClass() {
      $node= new ClassNode(MODIFIER_PUBLIC, array(), new TypeName('self'), NULL, array(), array(
        new FieldNode(array('name' => 'type', 'modifiers' => MODIFIER_PUBLIC)),
        new FieldNode(array('name' => 'name', 'modifiers' => MODIFIER_PUBLIC)),
      ));
      $this->assertVisited(array($node, $node->body[0], $node->body[1]), $node);
    }

    /**
     * Test visitClass()
     *
     */
    #[@test]
    public function visitClassWithEmptyBody() {
      $node= new ClassNode(MODIFIER_PUBLIC, array(), new TypeName('self'), NULL, array());
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitClone()
     *
     */
    #[@test]
    public function visitClone() {
      $node= new CloneNode(new VariableNode('this')); 
      $this->assertVisited(array($node, $node->expression), $node);
    }

    /**
     * Test visitComparison()
     *
     */
    #[@test]
    public function visitComparison() {
      $node= new ComparisonNode(array(
        'lhs' => new VariableNode('a'), 
        'rhs' => new IntegerNode(0), 
        'op'  => '=='
      ));
      $this->assertVisited(array($node, $node->lhs, $node->rhs), $node);
    }

    /**
     * Test visitConstant()
     *
     */
    #[@test]
    public function visitConstant() {
      $node= new ConstantNode('STDERR');
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitConstructor()
     *
     */
    #[@test]
    public function visitConstructor() {
      $node= new ConstructorNode(array(
        'modifiers'  => MODIFIER_PUBLIC,
        'annotations'=> NULL,
        'parameters' => NULL,
        'throws'     => NULL,
        'body'       => array(new VariableNode('a'), new ReturnNode()),
        'extension'  => NULL
      ));
      $this->assertVisited(array($node, $node->body[0], $node->body[1]), $node);
    }

    /**
     * Test visitContinue()
     *
     */
    #[@test]
    public function visitContinue() {
      $node= new ContinueNode();
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitDefault()
     *
     */
    #[@test]
    public function visitDefault() {
      $node= new DefaultNode(array('statements' => array(new ReturnNode())));
      $this->assertVisited(array($node, $node->statements[0]), $node);
    }

    /**
     * Test visitDecimal()
     *
     */
    #[@test]
    public function visitDecimal() {
      $node= new DecimalNode(1.0);
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitDo()
     *
     */
    #[@test]
    public function visitDo() {
      $node= new DoNode(new VariableNode('continue'), array(new VariableNode('a'), new ReturnNode()));
      $this->assertVisited(array($node, $node->statements[0], $node->statements[1], $node->expression), $node);
    }

    /**
     * Test visitElse()
     *
     */
    #[@test]
    public function visitElse() {
      $node= new ElseNode(array('statements' => array(new VariableNode('a'), new ReturnNode())));
      $this->assertVisited(array($node, $node->statements[0], $node->statements[1]), $node);
    }

    /**
     * Test visitEnumMember()
     *
     */
    #[@test]
    public function visitEnumMember() {
      $node= new EnumMemberNode(array('name' => 'penny', 'body' => array(new VariableNode('a'), new ReturnNode())));
      $this->assertVisited(array($node, $node->body[0], $node->body[1]), $node);
    }

    /**
     * Test visitEnumMember()
     *
     */
    #[@test]
    public function visitEnumMemberWithEmptyBody() {
      $node= new EnumMemberNode(array('name' => 'penny'));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitEnum()
     *
     */
    #[@test]
    public function visitEnum() {
      $node= new EnumNode(MODIFIER_PUBLIC, array(), new TypeName('Coin'), NULL, array(), array(
        new EnumMemberNode(array('name' => 'penny')),
        new EnumMemberNode(array('name' => 'dime')),
      ));
      $this->assertVisited(array($node, $node->body[0], $node->body[1]), $node);
    }

    /**
     * Test visitField()
     *
     */
    #[@test]
    public function visitField() {
      $node= new FieldNode(array('name' => 'type', 'modifiers' => MODIFIER_PUBLIC));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitField()
     *
     */
    #[@test]
    public function visitFieldWithInitialization() {
      $node= new FieldNode(array(
        'name'           => 'type', 
        'modifiers'      => MODIFIER_PUBLIC,
        'initialization' => new IntegerNode(0)
      ));
      $this->assertVisited(array($node, $node->initialization), $node);
    }

    /**
     * Test visitFinally()
     *
     */
    #[@test]
    public function visitFinally() {
      $node= new FinallyNode(array('statements' => array(new ReturnNode())));
      $this->assertVisited(array($node, $node->statements[0]), $node);
    }

    /**
     * Test visitFinally()
     *
     */
    #[@test]
    public function visitFinallyWithEmptyStatements() {
      $node= new FinallyNode(array('statements' => array()));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitFor()
     *
     */
    #[@test]
    public function visitFor() {
      $node= new ForNode(array(
        'initialization' => array(new VariableNode('a')),
        'condition'      => array(new VariableNode('b')),
        'loop'           => array(new VariableNode('c')),
        'statements'     => array(new VariableNode('d')), 
      ));
      $this->assertVisited(
        array($node, $node->initialization[0], $node->condition[0], $node->loop[0], $node->statements[0]), 
        $node
      );
    }

    /**
     * Test visitFor()
     *
     */
    #[@test]
    public function visitForWithEmptyStatements() {
      $node= new ForNode(array(
        'initialization' => array(new VariableNode('a')),
        'condition'      => array(new VariableNode('b')),
        'loop'           => array(new VariableNode('c')),
        'statements'     => NULL, 
      ));
      $this->assertVisited(
        array($node, $node->initialization[0], $node->condition[0], $node->loop[0]), 
        $node
      );
    }

    /**
     * Test visitForeach()
     *
     */
    #[@test]
    public function visitForeachWithKey() {
      $node= new ForeachNode(array(
        'expression'    => new VariableNode('list'),
        'assignment'    => array('value' => 'value'),
        'statements'    => array(new VariableNode('c')), 
      ));
      $this->assertVisited(
        array($node, $node->expression, new VariableNode('value'), $node->statements[0]), 
        $node
      );
    }

    /**
     * Test visitForeach()
     *
     */
    #[@test]
    public function visitForeachWithKeyAndValue() {
      $node= new ForeachNode(array(
        'expression'    => new VariableNode('map'),
        'assignment'    => array('key' => 'key', 'value' => 'value'),
        'statements'    => array(new VariableNode('c')), 
      ));
      $this->assertVisited(
        array($node, $node->expression, new VariableNode('key'), new VariableNode('value'), $node->statements[0]), 
        $node
      );
    }

    /**
     * Test visitForeach()
     *
     */
    #[@test]
    public function visitForeachWithEmptyStatements() {
      $node= new ForeachNode(array(
        'expression'    => new VariableNode('list'),
        'assignment'    => array('value' => 'value'),
        'statements'    => NULL, 
      ));
      $this->assertVisited(
        array($node, $node->expression, new VariableNode('value')), 
        $node
      );
    }

    /**
     * Test visitHex()
     *
     */
    #[@test]
    public function visitHex() {
      $node= new HexNode('0xFFFF');
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitIf()
     *
     */
    #[@test]
    public function visitIf() {
      $node= new IfNode(array(
        'condition'      => new VariableNode('i'),
        'statements'     => array(new ReturnNode()),
        'otherwise'      => NULL, 
      ));
      $this->assertVisited(
        array($node, $node->condition, $node->statements[0]), 
        $node
      );
    }

    /**
     * Test visitIf()
     *
     */
    #[@test]
    public function visitIfWithElse() {
      $node= new IfNode(array(
        'condition'      => new VariableNode('i'),
        'statements'     => array(new ReturnNode()),
        'otherwise'      => new ElseNode(array('statements' => array(new ReturnNode()))), 
      ));
      $this->assertVisited(
        array($node, $node->condition, $node->statements[0], $node->otherwise, $node->otherwise->statements[0]), 
        $node
      );
    }

    /**
     * Test visitIf()
     *
     */
    #[@test]
    public function visitIfWithEmptyStatements() {
      $node= new IfNode(array(
        'condition'      => new VariableNode('i'),
        'statements'     => NULL,
        'otherwise'      => NULL, 
      ));
      $this->assertVisited(
        array($node, $node->condition), 
        $node
      );
    }

    /**
     * Test visitImport()
     *
     */
    #[@test]
    public function visitImport() {
      $node= new ImportNode(array('name' => 'lang.types.String'));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitIndexer()
     *
     */
    #[@test]
    public function visitIndexer() {
      $node= new IndexerNode(array(
        'modifiers'  => MODIFIER_PUBLIC,
        'annotations'=> NULL,
        'type'       => new TypeName('T'),
        'parameter'  => array(
          'name'  => 'offset',
          'type'  => new TypeName('int'),
          'check' => TRUE
        ),
        'handlers'   => array(
          'get'   => array(new VariableNode('this')),
          'set'   => array(new ReturnNode()),
        )
      ));
      $this->assertVisited(array($node, $node->handlers['get'][0], $node->handlers['set'][0]), $node);
    }

    /**
     * Test visitIndexer()
     *
     */
    #[@test]
    public function visitIndexerWithEmptyHandlerBodies() {
      $node= new IndexerNode(array(
        'modifiers'  => MODIFIER_PUBLIC,
        'annotations'=> NULL,
        'type'       => new TypeName('T'),
        'parameter'  => array(
          'name'  => 'offset',
          'type'  => new TypeName('int'),
          'check' => TRUE
        ),
        'handlers'   => array(
          'get'   => NULL,
          'set'   => NULL
        )
      ));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitInstanceCreation()
     *
     */
    #[@test]
    public function visitInstanceCreation() {
      $node= new InstanceCreationNode(array(
        'type'       => new TypeName('self'), 
        'parameters' => array(new VariableNode('a'))
      ));
      $this->assertVisited(array($node, $node->parameters[0]), $node);
    }

    /**
     * Test visitInteger()
     *
     */
    #[@test]
    public function visitInteger() {
      $node= new IntegerNode(1);
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitInterface()
     *
     */
    #[@test]
    public function visitInterface() {
      $node= new InterfaceNode(MODIFIER_PUBLIC, array(), new TypeName('Strings'), array(), array(
        new ClassConstantNode('LF', new TypeName('string'), new StringNode('\n')),
        new ClassConstantNode('CR', new TypeName('string'), new StringNode('\r'))
      ));
      $this->assertVisited(
        array($node, $node->body[0], $node->body[0]->value, $node->body[1], $node->body[1]->value), 
        $node
      );
    }

    /**
     * Test visitInterface()
     *
     */
    #[@test]
    public function visitInterfaceWithEmptyBody() {
      $node= new InterfaceNode(MODIFIER_PUBLIC, array(), new TypeName('Strings'), array());
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitInstanceCreation()
     *
     */
    #[@test]
    public function visitInstanceCreationWithBody() {
      $node= new InstanceCreationNode(array(
        'type'       => new TypeName('self'), 
        'parameters' => array(new VariableNode('a')),
        'body'       => array(new ReturnNode()),
      ));
      $this->assertVisited(array($node, $node->parameters[0], $node->body[0]), $node);
    }

    /**
     * Test visitInvocation()
     *
     */
    #[@test]
    public function visitInvocation() {
      $node= new InvocationNode('create', array(new StringNode('new HashTable<string, string>')));
      $this->assertVisited(array($node, $node->arguments[0]), $node);
    }

    /**
     * Test visitLambda()
     *
     */
    #[@test]
    public function visitLambda() {
      $node= new LambdaNode(array(new VariableNode('a')), array(new ReturnNode()));
      $this->assertVisited(
        array($node, $node->parameters[0], $node->statements[0]), 
        $node
      );
    }

    /**
     * Test visitLambda()
     *
     */
    #[@test]
    public function visitLambdaWithEmptyStatements() {
      $node= new LambdaNode(array(new VariableNode('a')), array());
      $this->assertVisited(
        array($node, $node->parameters[0]), 
        $node
      );
    }

    /**
     * Test visitMap()
     *
     */
    #[@test]
    public function visitEmptyMap() {
      $node= new MapNode(array('elements' => NULL));
      $this->assertVisited(array($node), $node);
    }
 
    /**
     * Test visitMap()
     *
     */
    #[@test]
    public function visitMap() {
      $node= new MapNode(array('elements' => array(
        array(
          new StringNode('one'),
          new IntegerNode('1'),
        ),
      )));
      $this->assertVisited(
        array($node, $node->elements[0][0], $node->elements[0][1]), 
        $node
      );
    }

    /**
     * Test visitMethod()
     *
     */
    #[@test]
    public function visitMethod() {
      $node= new MethodNode(array(
        'modifiers'  => MODIFIER_PUBLIC,
        'annotations'=> NULL,
        'name'       => 'equals',
        'returns'    => new TypeName('bool'),
        'parameters' => array(array(
          'name'  => 'cmp',
          'type'  => new TypeName('Generic'),
          'check' => FALSE
        )),
        'throws'     => NULL,
        'body'       => array(new ReturnNode()),
        'extension'  => NULL
      ));
      $this->assertVisited(array($node, $node->body[0]), $node);
    }

    /**
     * Test visitMethod()
     *
     */
    #[@test]
    public function visitMethodWithEmptyBody() {
      $node= new MethodNode(array(
        'modifiers'  => MODIFIER_PUBLIC,
        'annotations'=> NULL,
        'name'       => 'equals',
        'returns'    => new TypeName('bool'),
        'parameters' => array(array(
          'name'  => 'cmp',
          'type'  => new TypeName('Generic'),
          'check' => FALSE
        )),
        'throws'     => NULL,
        'body'       => array(),
        'extension'  => NULL
      ));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitNativeImport()
     *
     */
    #[@test]
    public function visitNativeImport() {
      $node= new NativeImportNode(array('name' => 'pcre.*'));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitNoop()
     *
     */
    #[@test]
    public function visitNoop() {
      $node= new NoopNode();
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitNull()
     *
     */
    #[@test]
    public function visitNull() {
      $node= new NullNode();
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitOperator()
     *
     */
    #[@test]
    public function visitOperator() {
      $node= new OperatorNode(array(
        'modifiers'  => MODIFIER_PUBLIC | MODIFIER_STATIC,
        'annotations'=> NULL,
        'name'       => '',
        'symbol'     => '~',
        'returns'    => new TypeName('self'),
        'parameters' => array(
          array('name' => 'self', 'type' => new TypeName('self'), 'check' => TRUE),
          array('name' => 'arg', 'type' => TypeName::$VAR, 'check' => TRUE),
        ),
        'throws'     => NULL,
        'body'       => array(new ReturnNode()),
      ));
      $this->assertVisited(array($node, $node->body[0]), $node);
    }

    /**
     * Test visitOperator()
     *
     */
    #[@test]
    public function visitOperatorWithEmptyBody() {
      $node= new OperatorNode(array(
        'modifiers'  => MODIFIER_PUBLIC | MODIFIER_STATIC,
        'annotations'=> NULL,
        'name'       => '',
        'symbol'     => '~',
        'returns'    => new TypeName('self'),
        'parameters' => array(
          array('name' => 'self', 'type' => new TypeName('self'), 'check' => TRUE),
          array('name' => 'arg', 'type' => TypeName::$VAR, 'check' => TRUE),
        ),
        'throws'     => NULL,
        'body'       => array()
      ));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitPackage()
     *
     */
    #[@test]
    public function visitPackage() {
      $node= new PackageNode(array('name' => 'com.example.*'));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitProperty()
     *
     */
    #[@test]
    public function visitProperty() {
      $node= new PropertyNode(array(
        'modifiers'  => MODIFIER_PUBLIC,
        'annotations'=> NULL,
        'type'       => new TypeName('string'),
        'name'       => 'name',
        'handlers'   => array(
          'get' => array(new ReturnNode())
        )
      ));
      $this->assertVisited(array($node, $node->handlers['get'][0]), $node);
    }

    /**
     * Test visitProperty()
     *
     */
    #[@test]
    public function visitPropertyWithEmptyHandlerBody() {
      $node= new PropertyNode(array(
        'modifiers'  => MODIFIER_PUBLIC,
        'annotations'=> NULL,
        'type'       => new TypeName('string'),
        'name'       => 'name',
        'handlers'   => array(
          'get' => NULL
        )
      ));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitReturn()
     *
     */
    #[@test]
    public function visitReturn() {
      $node= new ReturnNode();
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitReturn()
     *
     */
    #[@test]
    public function visitReturnWithExpression() {
      $node= new ReturnNode(new VariableNode('a'));
      $this->assertVisited(array($node, $node->expression), $node);
    }

    /**
     * Test visitStatements()
     *
     */
    #[@test]
    public function visitStatements() {
      $node= new StatementsNode(array(new VariableNode('a'), new ReturnNode()));
      $this->assertVisited(array($node, $node->list[0], $node->list[1]), $node);
    }
   
    /**
     * Test visitStatements()
     *
     */
    #[@test]
    public function visitStatementsWithEmptyList() {
      $node= new StatementsNode();
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitStaticImport()
     *
     */
    #[@test]
    public function visitStaticImport() {
      $node= new StaticImportNode(array('name' => 'util.cmd.Console.*'));
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitStaticInitializer()
     *
     */
    #[@test]
    public function visitStaticInitializer() {
      $node= new StaticInitializerNode(array(new VariableNode('a'), new ReturnNode()));
      $this->assertVisited(array($node, $node->statements[0], $node->statements[1]), $node);
    }

    /**
     * Test visitStaticInitializer()
     *
     */
    #[@test]
    public function visitStaticInitializerWithEmptyStatements() {
      $node= new StaticInitializerNode(array());
      $this->assertVisited(array($node), $node);
    }
   
    /**
     * Test visitTernary()
     *
     */
    #[@test]
    public function visitTernary() {
      $node= new TernaryNode(array(
        'condition'   => new VariableNode('a'), 
        'expression'  => new VariableNode('a'), 
        'conditional' => new VariableNode('b')
      ));
      $this->assertVisited(
        array($node, $node->condition, $node->expression, $node->conditional), 
        $node
      );
    }

    /**
     * Test visitString()
     *
     */
    #[@test]
    public function visitString() {
      $node= new StringNode('Hello World');
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitSwitch()
     *
     */
    #[@test]
    public function visitSwitch() {
      $node= new SwitchNode(array(
        'expression'     => new VariableNode('i'),
        'cases'          => array(
          new DefaultNode(array('statements' => array(new BreakNode())))
        )
      ));
      $this->assertVisited(
        array($node, $node->expression, $node->cases[0], $node->cases[0]->statements[0]), 
        $node
      );
    }

    /**
     * Test visitTernary()
     *
     */
    #[@test]
    public function visitTernaryWithoutExpression() {
      $node= new TernaryNode(array(
        'condition'   => new VariableNode('a'), 
        'conditional' => new VariableNode('b')
      ));
      $this->assertVisited(
        array($node, $node->condition, $node->conditional), 
        $node
      );
    }

    /**
     * Test visitThrow()
     *
     */
    #[@test]
    public function visitThrow() {
      $node= new ThrowNode(array('expression' => new VariableNode('i')));
      $this->assertVisited(array($node, $node->expression), $node);
    }

    /**
     * Test visitTry()
     *
     */
    #[@test]
    public function visitTry() {
      $node= new TryNode(array(
        'statements' => array(new ReturnNode()), 
        'handling'   => array(new FinallyNode(array('statements' => array(new ReturnNode()))))
      ));
      $this->assertVisited(
        array($node, $node->statements[0], $node->handling[0], $node->handling[0]->statements[0]), 
        $node
      );
    }

    /**
     * Test visitUnaryOp()
     *
     */
    #[@test]
    public function visitUnaryOp() {
      $node= new UnaryOpNode(array(
        'expression'    => new VariableNode('i'),
        'op'            => '++',
        'postfix'       => TRUE
      ));
      $this->assertVisited(array($node, $node->expression), $node);
    }

    /**
     * Test visitVariable()
     *
     */
    #[@test]
    public function visitVariable() {
      $node= new VariableNode('i');
      $this->assertVisited(array($node), $node);
    }

    /**
     * Test visitWhile()
     *
     */
    #[@test]
    public function visitWhile() {
      $node= new WhileNode(new VariableNode('continue'), array(new VariableNode('a'), new ReturnNode()));
      $this->assertVisited(array($node, $node->expression, $node->statements[0], $node->statements[1]), $node);
    }
    
    /**
     * Test visitWith()
     *
     */
    #[@test]
    public function visitWith() {
      $node= new WithNode(array(new VariableNode('o')), array(new ReturnNode()));
      $this->assertVisited(array($node, $node->assignments[0], $node->statements[0]), $node);
    }

    /**
     * Test visitWith()
     *
     */
    #[@test]
    public function visitWithWithEmptyStatements() {
      $node= new WithNode(array(new VariableNode('o')), array());
      $this->assertVisited(array($node, $node->assignments[0]), $node);
    }

    /**
     * Test visitBracedExpression()
     *
     */
    #[@test]
    public function visitBracedExpression() {
      $node= new BracedExpressionNode(new VariableNode('o'));
      $this->assertVisited(array($node, $node->expression), $node);
    }

    /**
     * Parse sourcecode
     *
     * @param   string source
     * @return  xp.compiler.ast.TypeDeclarationNode
     */
    protected function parse($source) {
      return Syntax::forName('xp')->parse(new MemoryInputStream($source))->declaration;
    }
  
    /**
     * Test finding variables
     *
     */
    #[@test]
    public function findVariables() {
      $visitor= newinstance('xp.compiler.ast.Visitor', array(), '{
        public $variables= array();
        protected function visitVariable(VariableNode $var) {
          $this->variables[$var->name]= TRUE;
          return $var;
        }
      }');
      $visitor->visitOne($this->parse('class Test {
        public int add(int $a, int $b) {
          return $a + $b;
        }

        public int subtract(int $a, int $b) {
          return $a - $b;
        }
      }'));
      
      $this->assertEquals(array('a', 'b'), array_keys($visitor->variables));
    }

    /**
     * Test renaming variables
     *
     */
    #[@test]
    public function renameVariables() {
      $visitor= newinstance('xp.compiler.ast.Visitor', array(), '{
        protected $replacements;

        public function __construct() {
          $this->replacements= array("number" => new VariableNode("n"));
        }
        
        protected function visitMethod(MethodNode $node) {
          foreach ($node->parameters as $i => $parameter) {
            if (isset($this->replacements[$parameter["name"]])) {
              $node->parameters[$i]["name"]= $this->replacements[$parameter["name"]]->name;
            }
          }
          return parent::visitMethod($node);
        }

        protected function visitVariable(VariableNode $node) {
          return isset($this->replacements[$node->name])
            ? $this->replacements[$node->name]
            : $node
          ;
        }
      }');
      
      $this->assertEquals(
        $this->parse('class Fibonacci {
          public int fib(int $n) {
            return $n < 2 ? 1 : self::fib($n - 2) + self::fib($n - 1);
          }
        }'),
        $visitor->visitOne($this->parse('class Fibonacci {
          public int fib(int $number) {
            return $number < 2 ? 1 : self::fib($number - 2) + self::fib($number - 1);
          }
        }'))
      );
    }
  }
?>
