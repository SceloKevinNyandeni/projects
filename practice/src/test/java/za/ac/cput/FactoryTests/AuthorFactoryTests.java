package za.ac.cput.FactoryTests;

import org.junit.Test;
import za.ac.cput.Domain.Author;
import za.ac.cput.Domain.Book;

import java.util.Collections;

import static java.util.Collections.emptyList;
import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertNotNull;

public class AuthorFactoryTests {

    // This class will contain tests for the AuthorFactory
    // It will test the creation of Author objects using the AuthorFactory
    // The tests will ensure that the Author objects are created with the correct attributes
    @Test
    public void testCreateAuthor(){
      Author author = new Author.AuthorBuilder()
              .setAuthorId(1L)
              .setAuthorName("John Doe")
              .setBooks(Collections.singletonList(new Book.BookBuilder()
                      .setBookID(1L)
                      .setTitle("Sample Book")
                      .setYear(2023)
                      .setPrice(29.99)
                      .build()))
              .build();


        assertNotNull(author);
        assertEquals(Long.valueOf(1), author.getAuthorId());
        assertEquals("John Doe", author.getAuthorName());

        // Check that the book list has exactly one book
        assertNotNull(author.getBooks());
        assertEquals(1, author.getBooks().size());
        assertEquals("Sample Book", author.getBooks().get(0).getTitle());
    }

    @Test
    public void testCreateAuthorWithEmptyBooks() {
        Author author = new Author.AuthorBuilder()
                .setAuthorId(2L)
                .setAuthorName("Jane Smith")
                .setBooks(emptyList())
                .build();

        assertNotNull(author);
        assertEquals(Long.valueOf(2), author.getAuthorId());
        assertEquals("Jane Smith", author.getAuthorName());

        // Check that the book list is empty
        assertNotNull(author.getBooks());
        assertEquals(0, author.getBooks().size());
    }

    @Test
    public void testCreateAuthorWithNullBooks() {
        Author author = new Author.AuthorBuilder()
                .setAuthorId(3L)
                .setAuthorName("Alice Johnson")
                .setBooks(null) // Intentionally setting books to null
                .build();

        assertNotNull(author);
        assertEquals(Long.valueOf(3), author.getAuthorId());
        assertEquals("Alice Johnson", author.getAuthorName());

        // Check that the book list is null
        assertEquals(null, author.getBooks());
    }
}
