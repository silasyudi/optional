<?php

namespace SilasYudi\Optional;

use Throwable;

final class Optional
{
    /**
     * @var Optional|null common instance for {@code empty()}.
     */
    private static ?Optional $EMPTY = null;

    /**
     * @var mixed|null if non-null, the value; if null, indicates no value is present
     */
    private $value;

    /**
     * Constructs an instance with the described value, or empty instance if {@code $value} is {@code null}
     *
     * @param mixed $value value to describe
     */
    private function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * Returns an empty {@code Optional} instance. No value is present for this
     * {@code Optional}.
     *
     * @apiNote
     * Though it may be tempting to do so, avoid testing if an object is empty
     * by comparing with {@code ==} against instances returned by
     * {@code Optional.empty()}. There is no guarantee that it is a singleton.
     * Instead, use {@link #isPresent()}.
     *
     * @return Optional
     */
    public static function empty(): Optional
    {
        if (!self::$EMPTY) {
            self::$EMPTY = new Optional();
        }

        return self::$EMPTY;
    }

    /**
     * Constructs an instance with the described value.
     *
     * @param mixed $value the non-{@code null} value to describe
     * @throws OptionalInvalidStateException if value is {@code null}
     */
    public static function of($value): Optional
    {
        if ($value === null) {
            throw new OptionalInvalidStateException('The value is null.');
        }

        return new Optional($value);
    }

    /**
     * Returns an {@code Optional} describing the given value, if
     * non-{@code null}, otherwise returns an empty {@code Optional}.
     *
     * @param mixed $value the possibly-{@code null} value to describe
     * @return Optional an {@code Optional} with a present value if the specified value
     *         is non-{@code null}, otherwise an empty {@code Optional}
     */
    public static function ofNullable($value): Optional
    {
        return $value === null ? self::empty() : Optional::of($value);
    }

    /**
     * If a value is present, returns the value, otherwise throws
     * {@code OptionalInvalidStateException}.
     *
     * @apiNote
     * The preferred alternative to this method is {@link #orElseThrow()}.
     *
     * @return mixed the non-{@code null} value described by this {@code Optional}
     * @throws OptionalInvalidStateException if no value is present
     */
    public function get()
    {
        if ($this->value === null) {
            throw new OptionalInvalidStateException('No value present.');
        }

        return $this->value;
    }

    /**
     * If a value is present, returns {@code true}, otherwise {@code false}.
     *
     * @return bool {@code true} if a value is present, otherwise {@code false}
     */
    public function isPresent(): bool
    {
        return $this->value !== null;
    }

    /**
     * If a value is  not present, returns {@code true}, otherwise
     * {@code false}.
     *
     * @return bool {@code true} if a value is not present, otherwise {@code false}
     */
    public function isEmpty(): bool
    {
        return $this->value === null;
    }

    /**
     * If a value is present, performs the given action with the value,
     * otherwise does nothing.
     *
     * @param callable $action the action to be performed, if a value is present
     * @throws \TypeError if value is present and the given action is {@code null}
     */
    public function ifPresent(callable $action): void
    {
        if ($this->value !== null) {
            $action($this->value);
        }
    }

    /**
     * If a value is present, performs the given action with the value,
     * otherwise performs the given empty-based action.
     *
     * @param callable $action the action to be performed, if a value is present
     * @param callable $emptyAction the empty-based action to be performed, if no value is present
     * @throws \TypeError if a value is present and the given action is {@code null}, or no value is present
     *         and the given empty-based action is {@code null}.
     */
    public function ifPresentOrElse(callable $action, callable $emptyAction): void
    {
        if ($this->value !== null) {
            $action($this->value);
        } else {
            $emptyAction();
        }
    }

    /**
     * If a value is present, and the value matches the given predicate, returns an {@code Optional}
     * describing the value, otherwise returns an empty {@code Optional}.
     *
     * @param callable $filter the predicate to apply to a value, if present
     * @return Optional an {@code Optional} describing the value of this {@code Optional},
     *         if a value is present and the value matches the given predicate, otherwise an empty {@code Optional}
     * @throws \TypeError if the filter is {@code null}
     */
    public function filter(callable $filter): Optional
    {
        return $this->isPresent() && $filter($this->value) ? $this : Optional::empty();
    }

    /**
     * If a value is present, returns an {@code Optional} describing (as if by
     * {@link #ofNullable}) the result of applying the given mapping function to
     * the value, otherwise returns an empty {@code Optional}.
     *
     * If the mapping function returns a {@code null} result then this method
     * returns an empty {@code Optional}.
     *
     * @param callable $mapper the mapping function to apply to a value, if present
     * @return Optional an {@code Optional} describing the result of applying a mapping function to the value
     *         of this {@code Optional}, if a value is present, otherwise an empty {@code Optional}
     * @throws \TypeError if the mapping function is {@code null}
     */
    public function map(callable $mapper): Optional
    {
        if ($this->isPresent()) {
            return self::ofNullable($mapper($this->value));
        }

        return self::empty();
    }

    /**
     * If a value is present, returns the result of applying the given
     * {@code Optional}-bearing mapping function to the value, otherwise returns
     * an empty {@code Optional}.
     *
     * This method is similar to {@link #map(Function)}, but the mapping
     * function is one whose result is already an {@code Optional}, and if
     * invoked, {@code flatMap} does not wrap it within an additional
     * {@code Optional}.
     *
     * @param callable $mapper the mapping function to apply to a value, if present
     * @return mixed the result of applying an {@code Optional}-bearing mapping
     *         function to the value of this {@code Optional}, if a value is
     *         present, otherwise an empty {@code Optional}
     * @throws OptionalInvalidStateException if the mapping function returns a {@code null} result
     * @throws \TypeError if the mapping function is a {@code null}
     */
    public function flatMap(callable $mapper)
    {
        if (!$this->isPresent()) {
            return self::empty();
        }

        $result = $mapper($this->value);

        if ($result) {
            return $result;
        }

        throw new OptionalInvalidStateException('Null result from flat mapping.');
    }

    /**
     * If a value is present, returns an {@code Optional} describing the value,
     * otherwise returns an {@code Optional} produced by the supplying function.
     *
     * @param callable $action the supplying function that produces an {@code Optional} to be returned
     * @return Optional returns an {@code Optional} describing the value of this {@code Optional},
     *         if a value is present, otherwise an {@code Optional} produced by the supplying function.
     * @throws OptionalInvalidStateException if the action function returns a {@code null} result
     * @throws \TypeError if the action function is a {@code null} or result is not instance of {@code Optional}
     */
    public function or(callable $action): Optional
    {
        return $this->isPresent() ? $this : $action();
    }

    /**
     * If a value is present, returns the value, otherwise returns {@code other}.
     *
     * @param mixed $other the value to be returned, if no value is present. May be {@code null}.
     * @return mixed the value, if present, otherwise {@code other}
     */
    public function orElse($other)
    {
        return $this->value ?? $other;
    }

    /**
     * If a value is present, returns the value, otherwise returns the result
     * produced by the supplying function.
     *
     * @param callable $action the supplying function that produces a value to be returned
     * @return mixed the value, if present, otherwise the result produced by the supplying function
     * @throws \TypeError if no value is present and the supplying function is {@code null}
     */
    public function orElseGet(callable $action)
    {
        return $this->value ?? $action();
    }

    /**
     * If a value is present, returns the value, otherwise throws the exception specified in parameter or
     * {@code OptionalInvalidStateException}, if the exception is {@code null}.
     *
     * @param null|Throwable $throwable Type of the exception to be thrown, or {@code null}
     * @return mixed the value, if present
     * @throws Throwable if no value is present
     */
    public function orElseThrow(?Throwable $throwable = null)
    {
        if ($this->value === null) {
            throw $throwable ?? new OptionalInvalidStateException('No value present.');
        }

        return $this->value;
    }

    /**
     * Indicates whether some other object is "equal to" this {@code Optional}.
     * The other object is considered equal if:
     *
     * <ul>
     * <li>it is also an {@code Optional} and;
     * <li>both instances have no value present or;
     * <li>the present values are "equal to" each other via {@code equals()}.
     * </ul>
     *
     * @param mixed $object an object to be tested for equality
     * @return bool {@code true} if the other object is "equal to" this object otherwise {@code false}
     */
    public function equals($object): bool
    {
        return $object === $this || ($object instanceof self && $object->value === $this->value);
    }

    /**
     * Returns a non-empty string representation of this {@code Optional} suitable for debugging.
     * The exact presentation format is unspecified and may vary between implementations and versions.
     *
     * @return string the string representation of this instance
     */
    public function __toString(): string
    {
        return $this->value !== null
            ? sprintf("Optional[%s]", $this->value)
            : "Optional.empty";
    }
}
