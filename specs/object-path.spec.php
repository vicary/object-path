<?php
use Peridot\ObjectPath\ObjectPath;

describe('ObjectPath', function() {

    context('when using an object', function() {
        beforeEach(function() {
            $object = new stdClass();
            $object->name = new stdClass();
            $object->name->first = "brian";
            $object->name->last = "scaturro";
            $object->projects = [
                'php' => ['peridot', 'leo'],
                'coffeescript' => ['alerts', 'pressbox'],
            ];
            $this->object = $object;

            $this->path = new ObjectPath($this->object);
        });

        describe('->get()', function() {
            it('should be able to get a nested value', function() {
                $first = $this->path->get('name->first');
                expect($first->getPropertyValue())->to->equal('brian');
            });

            it('should return last value if it is an object', function() {
                $this->object->name->origin = new stdClass();
                $this->object->name->origin->country = "Ireland";
                $origin = $this->path->get('name->origin');
                expect($origin->getPropertyValue())->to->equal($this->object->name->origin->country);
            });

            it('should return array properties', function() {
                $peridot = $this->path->get('projects[php][0]');
                expect($peridot->getPropertyValue())->to->equal('peridot');
            });

            it('should return null if property does not exist', function() {
                expect($this->path->get('nickname'))->to->be->null;
            });
        });

        describe('->__get()', function() {
            it('should be able to get a nested value', function() {
                $first = $this->path->{'name->first'};
                expect($first)->to->equal('brian');
            });

            it('should return last value if it is an object', function() {
                $this->object->name->origin = new stdClass();
                $this->object->name->origin->country = 'Ireland';
                $origin = $this->path->{'name->origin'};
                expect($origin)->to->equal($this->object->name->origin);
            });

            it('should return array properties', function() {
                $peridot = $this->path->{'projects[php][0]'};
                expect($peridot)->to->equal('peridot');
            });

            it('should return null if property does not exist', function() {
                expect($this->path->{'nickname'})->to->be->null;
            });
        });

        describe('->set()', function() {
            it('should be able to update a nested value', function() {
                $this->path->{'name->first'} = 'john';
                expect($this->object->name->first)->to->equal('john');
            });

            it('should updates array properties', function() {
                $this->path->{'projects[php][0]'} = 'object-path';
                expect($this->object->projects['php'][0])->to->equal('object-path');
            });

            it('should do nothing if a nested property does not exist', function() {
                $this->path->{'address->city'} = '1234 Lane';
                expect($this->path->{'address->city'})->to->be->null;
            });
        });

        describe('->unset()', function() {
            it('should be able to unset a nested object value', function() {
                unset($this->path->{'name->first'});
                expect($this->path->{'name->first'})->to->be->null;
            });

            it('should be able to unset a nested array value', function() {
                unset($this->path->{'projects->php[1]'});
                expect($this->path->{'projects->php[1]'})->to->be->null;
            });

            it('should be able to unset a nested object', function() {
                unset($this->path->{'name'});
                expect($this->path->name)->to->be->null;
            });

            it('should do nothing if property does not exist', function() {
                unset($this->path->{'nickname'});
                expect($this->path->{'nickname'})->to->be->null;
            });
        });
    });

    context('when using an array', function() {
        beforeEach(function() {
            $this->array = [
                'name' => [
                    'first' => 'brian',
                    'last' => 'scaturro'
                ],
                'string',
                1
            ];
            $this->path = new ObjectPath($this->array);
        });

        it('should be able to get an array value', function() {
            $one = $this->path->get('[1]');
            expect($one->getPropertyValue())->to->equal(1);
        });

        it('should be able to get nested values', function() {
            $name = $this->path->get('[name][first]');
            expect($name->getPropertyValue())->to->equal('brian');
            expect($name->getPropertyName())->to->equal('first');
        });
    });

});
